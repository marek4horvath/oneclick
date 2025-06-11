<script setup lang="ts">
import ForgotPassword from '@/dialogs/auth/forgot-password.vue'

const authStore = useAuthStore()
const { $event } = useNuxtApp()

const form = ref(null)

const valid = ref(false)
const email = ref('')
const password = ref('')
const rememberMe = ref(false)

const emailRules = [
    (v: string) => !!v || 'E-mail is required',
    (v: string) => /.+@.+/.test(v) || 'E-mail must be valid',
]

const passwordRules = [
    (v: string) => !!v || 'Password is required',
]

onMounted(async () => {
    if (authStore.isLoggedIn) {
        navigateTo('/dashboard')
    }
})

const submit = async function () {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const payload = {
        email: email.value,
        password: password.value,
        rememberMe: rememberMe.value,
    }

    await authStore.login(payload)
}

const forgotPassword = () => {
    $event('openForgotPasswordModal')
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
                    {{ $t('login.title') }}
                </span>
                <span class="d-block text-body-2 text-secondary text-center mb-4">
                    {{ $t('login.subTitle') }}
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
                                    v-model="email"
                                    :label="$t('login.email')"
                                    type="email"
                                    required
                                    variant="outlined"
                                    :rules="emailRules"
                                />
                            </VCol>
                        </VRow>

                        <VRow>
                            <VCol>
                                <VTextField
                                    v-model="password"
                                    :label="$t('login.password')"
                                    type="password"
                                    required
                                    variant="outlined"
                                    :rules="passwordRules"
                                />

                                <VBtn
                                    type="button"
                                    class="d-inline-block px-0"
                                    variant="plain"
                                    @click.prevent="forgotPassword"
                                >
                                    {{ $t('login.forgot_password') }}
                                </VBtn>
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
                        <VCol class="ma-0 pa-0">
                            <VCheckbox
                                v-model="rememberMe"
                                density="comfortable"
                                :label="$t('login.remember')"
                            />
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
                                {{ $t('login.login') }}
                            </VBtn>
                        </VCol>
                    </VRow>
                </VContainer>
            </VCardActions>
        </VCard>
    </div>

    <ForgotPassword />
</template>

<style scoped>
.text-h4 {
    color: #65c09e !important;
    font-size: 20px !important;
}
</style>

<style>
.v-checkbox .v-input__details {
    display: none !important;
}
</style>
