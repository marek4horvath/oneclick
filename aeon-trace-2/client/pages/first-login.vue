<script setup lang="ts">
import type { MessageBag } from '~/types'

definePageMeta({
    title: 'registration.firstLogin',
    name: 'first-login',
})

const { $event } = useNuxtApp()

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const authStore = useAuthStore()
const password = ref<string>('')
const agreeToGdpr = ref(false)
const valid = ref(false)
const form = ref(null)
const token = ref('')

const passwordRules = [
    (v: string) => !!v || 'Password is required',
    (v: string) =>
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/.test(v)
        || t('messages.validResetPass'),
]

const gdprRules = [
    (v: boolean) => v || 'You must agree to the GDPR terms',
]

watch(() => token.value, () => {
    if (!token.value || token.value.trim() === '') {
        const message: MessageBag = {
            type: 'error',
            message: 'Missing token',
            title: 'Missing Token',
        }

        $event('message', message)
        router.push('/login')
    }
})

onMounted(() => {
    token.value = route.query.token as string | undefined
})

const submit = async function () {
    const formValidation: any = form.value

    await formValidation.validate()

    if (!valid.value) {
        return
    }

    const payload = {
        token: token.value,
        password: password.value,
    }

    await authStore.userRegistration(payload)
}
</script>

<template>
    <div class="w-screen h-screen d-flex align-center justify-center">
        <VCard
            width="500"
            max-width="50%"
            variant="elevated"
            class="bg-surface-light"
        >
            <template #title>
                <VImg
                    src="/assets/images/logo.png"
                    class="mx-auto w-50 my-4"
                />
            </template>

            <VCardText class="bg-white pt-4">
                <span class="d-block text-h4 text-center mb-4">
                    {{ t('registration.title') }} &#128521;
                </span>
                <span class="d-block text-body-2 text-secondary text-center mb-4">
                    {{ t('registration.subtitle') }}
                </span>

                <VForm
                    ref="form"
                    v-model="valid"
                    @keyup.enter="submit"
                >
                    <VContainer>
                        <VRow no-gutters>
                            <VCol>
                                <VTextField
                                    v-model="password"
                                    :label="t('login.password')"
                                    type="password"
                                    required
                                    variant="outlined"
                                    :rules="passwordRules"
                                />
                            </VCol>
                        </VRow>
                    </VContainer>
                </VForm>
            </VCardText>

            <VCardActions>
                <VContainer>
                    <VRow
                        justify="end"
                        align="center"
                        class="ma-0"
                    >
                        <VCol class="ma-0 pa-0 me-6">
                            <VCheckbox
                                v-model="agreeToGdpr"
                                density="comfortable"
                                :rules="gdprRules"
                            >
                                <template #label>
                                    <span class="size-of-checkbox-text">{{ t('registration.GDPR') }}</span>
                                </template>
                            </VCheckbox>
                        </VCol>

                        <VCol
                            cols="auto"
                            class="ma-0 pa-0"
                        >
                            <VBtn
                                color="primary"
                                :disabled="!valid"
                                @click="submit"
                            >
                                {{ t('registration.register') }}
                            </VBtn>
                        </VCol>
                    </VRow>
                </VContainer>
            </VCardActions>
        </VCard>
    </div>
</template>

<style scoped>
.text-h4 {
    color: #65c09e !important;
    font-size: 20px !important;
}

.size-of-checkbox-text {
    font-size: 12px !important;
}
</style>

<style>
.v-checkbox .v-input__details {
    display: none !important;
}
</style>
