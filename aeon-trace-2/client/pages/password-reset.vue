<script setup lang="ts">
definePageMeta({
    title: 'login.resetPassword',
    name: 'password-reset',
})

const { $alert } = useNuxtApp()
const { t } = useI18n()
const authStore = useAuthStore()
const route = useRoute()

const form = ref(null)

const valid = ref(false)
const password = ref('')
const passwordAgain = ref('')
const showPassword = ref(false)
const showPasswordAgain = ref(false)

const passwordRules = [
    (v: string) => !!v || 'Password is required',
    (v: string) =>
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/.test(v)
        || t('messages.validResetPass'),
    (v: string) => v === password.value || 'Passwords do not match',
]

onMounted(async () => {
    if (authStore.isLoggedIn) {
        navigateTo('/dashboard')
    }
})

const submit = async function () {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value || !route.query.token) {
        return
    }

    await authStore.resetPassword(route.query.token as string, password.value)

    $alert('Password reset successfully')

    navigateTo('/login')
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
                    {{ $t('login.resetPassword') }}

                </span>
                <span class="d-block text-body-2 text-secondary text-center mb-4">
                    {{ $t('login.subTitleReset') }}
                </span>

                <VForm
                    ref="form"
                    v-model="valid"
                    @keyup.enter="submit"
                >
                    <VContainer fluid>
                        <VRow no-gutters>
                            <VCol>
                                <VTextField
                                    v-model="password"
                                    :label="$t('login.password')"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    variant="outlined"
                                    :rules="passwordRules"
                                >
                                    <template #append-inner>
                                        <PhosphorIconEye
                                            v-if="!showPassword"
                                            class="cursor-pointer"
                                            size="20"
                                            color="#7d7d7d"
                                            weight="bold"
                                            @click="showPassword = !showPassword"
                                        />
                                        <PhosphorIconEyeSlash
                                            v-else
                                            class="cursor-pointer"
                                            size="20"
                                            color="#7d7d7d"
                                            weight="bold"
                                            @click="showPassword = !showPassword"
                                        />
                                    </template>
                                </VTextField>
                            </VCol>
                        </VRow>

                        <VRow>
                            <VCol>
                                <VTextField
                                    v-model="passwordAgain"
                                    :label="$t('login.passwordAgain')"
                                    :type="showPasswordAgain ? 'text' : 'password'"
                                    required
                                    variant="outlined"
                                    :rules="passwordRules"
                                >
                                    <template #append-inner>
                                        <PhosphorIconEye
                                            v-if="!showPasswordAgain"
                                            class="cursor-pointer"
                                            size="20"
                                            color="#7d7d7d"
                                            weight="bold"
                                            @click="showPasswordAgain = !showPasswordAgain"
                                        />
                                        <PhosphorIconEyeSlash
                                            v-else
                                            class="cursor-pointer"
                                            size="20"
                                            color="#7d7d7d"
                                            weight="bold"
                                            @click="showPasswordAgain = !showPasswordAgain"
                                        />
                                    </template>
                                </VTextField>
                            </VCol>
                        </VRow>
                    </VContainer>
                </VForm>
            </VCardText>

            <VCardActions>
                <VContainer fluid>
                    <VRow
                        justify="end"
                        align="center"
                        class="ma-0"
                    >
                        <VCol
                            cols="auto"
                            class="ma-0 pa-0"
                        >
                            <VBtn
                                color="primary"
                                :disabled="!valid"
                                @click="submit"
                            >
                                {{ $t('login.resetPassword') }}
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
</style>

<style>
.v-checkbox .v-input__details {
    display: none !important;
}
</style>
