<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen } = useNuxtApp()

const isInviteCompanyModalOpen = ref(false)
const form = ref(null)
const email = ref('')
const companiesStore = useCompaniesStore()

const emailRules = [
    (v: string) => !!v || 'E-mail is required',
    (v: string) => /.+@.+/.test(v) || 'E-mail must be valid',
]

const valid = ref(false)

$listen('openInviteCompanyModal', async () => {
    isInviteCompanyModalOpen.value = true
    email.value = ''
})

const closeInviteCompanyModal = () => {
    isInviteCompanyModalOpen.value = false
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const inviteCompany = await companiesStore.inviteCompany(email.value)

    if (!inviteCompany) {
        return
    }

    closeInviteCompanyModal()
}
</script>

<template>
    <ModalLayout
        :is-open="isInviteCompanyModalOpen"
        name="invite-company-modal"
        title="Invite company"
        button-submit-text="Save"
        class="invite-company"
        @modal-close="closeInviteCompanyModal"
        @submit="submitHandler"
    >
        <template #description>
            {{ $t('companies.inviteCompanyTitle') }}
        </template>
        <template #content>
            <VForm
                ref="form"
                v-model="valid"
            >
                <VTextField
                    v-model="email"
                    :label="$t('companies.email')"
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
                height="45"
                @click="submitHandler"
            >
                {{ $t('companies.inviteCompany') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.invite-company.modal-mask {
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
