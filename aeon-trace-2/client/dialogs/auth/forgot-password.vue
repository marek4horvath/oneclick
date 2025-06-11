<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

definePageMeta({
    title: 'page.forgotPassword.title',
    name: 'forgot-password',
})

const authStore = useAuthStore()
const { $listen } = useNuxtApp()

const isForgotPasswordModalOpen = ref(false)
const form = ref(null)
const email = ref('')

const emailRules = [
    (v: string) => !!v || 'E-mail is required',
    (v: string) => /.+@.+/.test(v) || 'E-mail must be valid',
]

const valid = ref(false)

$listen('openForgotPasswordModal', async () => {
    isForgotPasswordModalOpen.value = true
    email.value = ''
})

const closeForgotPasswordModal = () => {
    isForgotPasswordModalOpen.value = false
}

const submitHandler = async () => {
    await authStore.forgotPassword(email.value)

    closeForgotPasswordModal()
}
</script>

<template>
    <ModalLayout
        :is-open="isForgotPasswordModalOpen"
        name="reset-password-modal"
        title="Forgot password"
        button-submit-text="Save"
        class="reset-password"
        @modal-close="closeForgotPasswordModal"
        @submit="submitHandler"
    >
        <template #description>
            {{ $t('login.forgot_password_description') }}
        </template>
        <template #content>
            <VForm
                ref="form"
                v-model="valid"
            >
                <VTextField
                    v-model="email"
                    :label="$t('login.email')"
                    variant="outlined"
                    density="compact"
                    type="text"
                    :rules="emailRules"
                />
            </VForm>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                @click="submitHandler"
            >
                {{ $t('login.resetPassword') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.reset-password.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        :global(.modal-footer) {
            margin-top: 2rem;
        }

        .modal-footer {
            .v-btn {
                padding-inline: 1rem;
                padding-block: 0.5rem;
                display: inline-block;
                border-radius: unset;
                flex: 1;
                transition: 0.5s all;

                &:hover {
                    background-color: rgba(167, 217, 212, 1) !important;
                    color: #000000 !important;
                }

                &.submit-btn {
                    background-color: rgba(38, 166, 154, 1);
                    color: #FFFFFF;
                    transition: 0.5s all;

                    &:hover {
                        background-color: rgba(167, 217, 212, 1);
                    }
                }
            }
        }
    }
}

.v-overlay-container {
    z-index: 9999 !important;
}
</style>
