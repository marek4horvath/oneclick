<script setup lang="ts">
import type { MessageBag } from '~/types'

definePageMeta({
    title: 'Company Registration',
    name: 'registration-company',
})

const route = useRoute()
const router = useRouter()

const { t } = useI18n()
const { $event } = useNuxtApp()

const authStore = useAuthStore()
const countriesStore = useCountriesStore()

const registerForm = ref({
    name: '',
    address: {
        street: '',
        houseNo: '',
        city: '',
        postcode: '',
        country: null,
    },
    email: '',
    phone: '',
    password: '',
    token: '',
})

const countries = ref([])

const agreeToGdpr = ref(false)
const valid = ref(false)
const form = ref(null)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const streetRules = [
    (v: string) => !!v || 'Street is required',
    (v: string) => v.trim().length > 0 || 'Street cannot be empty',
]

const zipRules = [
    (v: string) => !!v || 'Zip is required',
    (v: string) => v.trim().length > 0 || 'Zip cannot be empty',
]

const houseNumberRules = [
    (v: string) => !!v || 'House number is required',
    (v: string) => v.trim().length > 0 || 'House number cannot be empty',
]

const cityRules = [
    (v: string) => !!v || 'City is required',
    (v: string) => v.trim().length > 0 || 'Zip cannot be empty',
]

const countryRules = [
    (v: string) => !!v || 'Country is required',
    (v: string) => v.trim().length > 0 || 'Country cannot be empty',
]

const emailRules = [
    (v: string) => !!v || 'E-mail is required',
    (v: string) => /.+@.+/.test(v) || 'E-mail must be valid',
]

const contactRules = [
    (v: string) => !!v || 'Phone number is required',
    (v: string) => /^\d+$/.test(v) || 'Phone number must contain only numbers',
    (v: string) => v.length >= 8 || 'Phone number must be at least 8 digits long',
    (v: string) => v.length <= 15 || 'Phone number must be at most 15 digits long',
]

const passwordRules = [
    (v: string) => !!v || 'Password is required',
    (v: string) =>
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/.test(v)
        || t('messages.validResetPass'),
]

const gdprRules = [
    (v: boolean) => v || 'You must agree to the GDPR terms',
]

watch(() => registerForm.value.token, () => {
    if (!registerForm.value.token || registerForm.value.token.trim() === '') {
        const message: MessageBag = {
            type: 'error',
            message: 'Missing token',
            title: 'Missing Token',
        }

        $event('message', message)
        router.push('/login')
    }
})

onMounted(async () => {
    registerForm.value.token = route.query.token as string | undefined
    registerForm.value.email = route.query.email as string | undefined

    await countriesStore.fetchCountries()
    countries.value = countriesStore.getCountries
})

const submit = async function () {
    const formValidation: any = form.value

    await formValidation.validate()

    if (!valid.value) {
        return
    }

    const payload = {
        name: registerForm.value.name,
        street: registerForm.value.address.street,
        houseNo: registerForm.value.address.houseNo,
        city: registerForm.value.address.city,
        postcode: registerForm.value.address.postcode,
        country: registerForm.value.address.country,
        email: registerForm.value.email,
        phone: registerForm.value.phone,
        password: registerForm.value.password,
        token: registerForm.value.token,
    }

    await authStore.companyRegistration(payload)
}
</script>

<template>
    <div class="w-screen h-screen d-flex align-center justify-center">
        <VCard
            width="700"
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
                    {{ t('registration.company.title') }} &#128640;
                </span>
                <span class="d-block text-body-2 text-secondary text-center mb-4">
                    {{ t('registration.company.subtitle') }}
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
                                    v-model="registerForm.name"
                                    :label="t('registration.company.name')"
                                    required
                                    variant="outlined"
                                    :rules="nameRules"
                                />
                            </VCol>
                        </VRow>
                        <VRow class="mt-0">
                            <VCol>
                                <VTextField
                                    v-model="registerForm.address.street"
                                    :label="t('registration.company.address.street')"
                                    required
                                    variant="outlined"
                                    :rules="streetRules"
                                />
                            </VCol>
                            <VCol>
                                <VTextField
                                    v-model="registerForm.address.houseNo"
                                    :label="t('registration.company.address.houseNo')"
                                    required
                                    variant="outlined"
                                    :rules="houseNumberRules"
                                />
                            </VCol>
                        </VRow>
                        <VRow class="mt-0">
                            <VCol>
                                <VTextField
                                    v-model="registerForm.address.city"
                                    :label="t('registration.company.address.city')"
                                    required
                                    variant="outlined"
                                    :rules="cityRules"
                                />
                            </VCol>
                            <VCol>
                                <VTextField
                                    v-model="registerForm.address.postcode"
                                    :label="t('registration.company.address.postcode')"
                                    required
                                    variant="outlined"
                                    :rules="zipRules"
                                />
                            </VCol>
                        </VRow>
                        <VRow no-gutters>
                            <VCol>
                                <VSelect
                                    v-model="registerForm.address.country"
                                    :items="countries"
                                    :label="t('registration.company.address.country')"
                                    required
                                    variant="outlined"
                                    :rules="countryRules"
                                />
                            </VCol>
                        </VRow>
                        <VRow class="mt-0">
                            <VCol>
                                <VTextField
                                    v-model="registerForm.email"
                                    :label="t('registration.company.email')"
                                    type="email"
                                    required
                                    variant="outlined"
                                    :rules="emailRules"
                                />
                            </VCol>
                            <VCol>
                                <VTextField
                                    v-model="registerForm.phone"
                                    :label="t('registration.company.phone')"
                                    required
                                    variant="outlined"
                                    :rules="contactRules"
                                />
                            </VCol>
                        </VRow>
                        <VRow no-gutters>
                            <VCol>
                                <VTextField
                                    v-model="registerForm.password"
                                    :label="t('registration.company.password')"
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
