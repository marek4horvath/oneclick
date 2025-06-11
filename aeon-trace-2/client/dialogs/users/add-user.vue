<script setup lang="ts">
import { uniqBy } from 'lodash'
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen } = useNuxtApp()
const { $event } = useNuxtApp()
const { t } = useI18n()
const isAddUserModalOpen = ref(false)

const form = ref(null)
const valid = ref(false)

const formUser = ref({
    id: '',
    firstName: '',
    lastName: '',
    email: '',
    role: null,
})

const companyId = ref('')

const rolesStore = useRolesStore()
const authStore = useAuthStore()

const rolesItems = ref<string[]>([])
const rolesData = computed(() => rolesStore.roles)

const firstNameRules = [
    (v: string) => !!v || 'First name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const lastRules = [
    (v: string) => !!v || 'Last name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const roleRules = [
    (v: string | string[]) => (v && v.length) ? true : 'Role is required',
    (v: string | string[]) => (Array.isArray(v) && v.length > 0) ? true : 'Roles cannot be empty',
]

const emailRules = [
    (v: string) => !!v || 'E-mail is required',
    (v: string) => /.+@.+/.test(v) || 'E-mail must be valid',
]

$listen('openAddUserModal', async (id?: string) => {
    isAddUserModalOpen.value = true
    companyId.value = id
    await rolesStore.fetchRoles()

    const availableRolesMapping = {
        ROLE_COMPANY_MANAGER: t('roles.companyManager'),
        ROLE_COMPANY_USER: t('roles.companyUser'),
    }

    const enabledRoles = Object.keys(availableRolesMapping)

    rolesItems.value = rolesData.value[0].roles[0]
        .filter((val: string) => enabledRoles.includes(val))
        .map((val: string) => {
            return {
                title: availableRolesMapping[val as keyof typeof availableRolesMapping],
                value: val,
            }
        })
})

const closeAddUserModal = () => {
    isAddUserModalOpen.value = false
}

const handleUsersSubmitted = () => {
    $event('handleUserSubmitted')
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    await authStore.registrationUser({
        email: formUser.value.email,
        firstName: formUser.value.firstName,
        lastName: formUser.value.lastName,
        company: `/api/companies/${companyId.value}`,
        roles: formUser.value.role,
    })

    handleUsersSubmitted()
    isAddUserModalOpen.value = false
}
</script>

<template>
    <ModalLayout
        :is-open="isAddUserModalOpen"
        name="add-user-modal"
        :title="$t('users.addHeader')"
        button-submit-text="Save"
        class="add-user"
        @modal-close="closeAddUserModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="form-wrapper">
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VTextField
                        v-model="formUser.firstName"
                        :label="$t('users.firstName')"
                        variant="outlined"
                        density="compact"
                        type="text"
                        :rules="firstNameRules"
                    />

                    <VTextField
                        v-model="formUser.lastName"
                        :label="$t('users.lastName')"
                        variant="outlined"
                        density="compact"
                        type="text"
                        :rules="lastRules"
                    />

                    <VSelect
                        v-model="formUser.role"
                        :label="$t('users.role')"
                        :placeholder="$t('users.selectRole')"
                        :items="uniqBy(rolesItems, 'value')"
                        class="custom-select"
                        multiple
                        :rules="roleRules"
                        variant="outlined"
                    />

                    <VTextField
                        v-model="formUser.email"
                        :label="$t('users.email')"
                        variant="outlined"
                        density="compact"
                        type="text"
                        :rules="emailRules"
                    />
                </VForm>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                height="45"
                @click="submitHandler"
            >
                {{ $t('users.addUser') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style lang="scss">
.add-user.modal-mask {

    .modal-container {
        width: 30vw !important;

        .modal-body {
            height: auto;
            padding-top: 1rem;
            .form-wrapper {
                max-height: 400;
                padding-top: 1rem;
                overflow-y: auto;
            }
        }

        .modal-footer {
            margin-top: 2rem;
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

.custom-swatch {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid #fff;
  cursor: pointer;
}
</style>
