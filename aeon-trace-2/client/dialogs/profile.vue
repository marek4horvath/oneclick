<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen } = useNuxtApp()

const isProfileModalOpen = ref(false)

$listen('openProfileModal', () => {
    isProfileModalOpen.value = true
})

const closeProfileModal = () => {
    isProfileModalOpen.value = false
}

const submitHandler = () => {
    // here you do whatever
}
</script>

<template>
    <ModalLayout
        :is-open="isProfileModalOpen"
        name="profile-modal"
        title="Profile"
        button-cancel-text="Cancel"
        button-submit-text="Save"
        @modal-close="closeProfileModal"
        @submit="submitHandler"
    >
        <template #description>
            You can review and update your profile information <br>
            and email preferences.
        </template>

        <template #content>
            <VForm>
                <VTextField
                    label="Name"
                    variant="outlined"
                    density="compact"
                    type="text"
                />

                <VTextField
                    label="Email"
                    variant="outlined"
                    density="compact"
                    type="email"
                />
            </VForm>
        </template>
    </ModalLayout>
</template>
