<template>
    <div class="w-screen h-screen d-flex align-center justify-center">
        <v-card width="500" max-width="50%" variant="elevated" class="bg-surface-light">
            <template v-slot:title>
                <v-img src="/assets/images/logo.png" class="mx-auto w-50 my-4"></v-img>
            </template>

            <v-card-text class="bg-white pt-4">
                <span class="d-block text-body-2 text-secondary text-center mb-4">
                    Please sign-in to your account and start the adventure
                </span>

                <v-form ref="form" v-model="valid" @keyup.native.enter="submit">
                    <v-container>

                        <v-row no-gutters>
                            <v-col>
                                <v-text-field v-model="email" :label="$t('login.email')" type="email" required
                                    :rules="emailRules"></v-text-field>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col>
                                <v-text-field v-model="password" :label="$t('login.password')" type="password" required
                                    :rules="passwordRules"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-form>
            </v-card-text>

            <v-card-actions>
                <v-container>
                    <v-row justify="end" align="center" class="ma-0">
                        <v-col class="ma-0 pa-0">
                            <v-checkbox density="comfortable" v-model="rememberMe" :label="$t('login.remember')">
                            </v-checkbox>
                        </v-col>

                        <v-col cols="auto" class="ma-0 pa-0">
                            <v-btn color="primary" :disabled="!valid" @click="submit">{{ $t('login.login') }}</v-btn>
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-actions>
        </v-card>
    </div>
</template>

<script setup lang="ts">
const { $toast } = useNuxtApp()

const authStore = useAuthStore()

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

onMounted(() => {
    if (authStore.isLoggedIn) {
        navigateTo('/dashboard')
    }
})

const submit = function () {
    const formValidation: any = form.value
    formValidation.validate()

    if (!valid.value) {
        return
    }

    if (email.value !== 'marko@grownapps.io') {
        $toast.error('Invalid credentials');
        return;
    }

    const payload = {
        email: email.value,
        password: password.value,
        rememberMe: rememberMe.value,
    }

    authStore.login(payload)

    navigateTo('/dashboard')
}
</script>

<style>
.v-checkbox .v-input__details {
    display: none !important;
}
</style>
