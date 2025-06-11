<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $event, $listen } = useNuxtApp()
const isEditInputCategoryModalOpen = ref(false)
const inputCategoriesStore = useInputCategoriesStore()
const form = ref(null)

const formCategory = ref({
    id: '',
    name: '',
})

const valid = ref(false)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

$listen('openEditInputCategoryModal', category => {
    formCategory.value = {
        id: category.id,
        name: category.name,
    }
    isEditInputCategoryModalOpen.value = true
})

const closeEditInputCategorytModal = () => {
    isEditInputCategoryModalOpen.value = false
}

const handleCategorySubmitted = () => {
    $event('handleCategorySubmitted')
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const inputCategories = await inputCategoriesStore.updateInputCategory(
        formCategory.value.id,
        {
            name: formCategory.value.name,
        },
    )

    if (!inputCategories) {
        return
    }

    handleCategorySubmitted()
    isEditInputCategoryModalOpen.value = false
}
</script>

<template>
    <ModalLayout
        :is-open="isEditInputCategoryModalOpen"
        name="edit-input-category-modal"
        :title="$t('category.editCategory')"
        button-submit-text="Save"
        class="link-product"
        @modal-close="closeEditInputCategorytModal"
        @submit="submitHandler"
    >
        <template #content>
            <VForm
                ref="form"
                v-model="valid"
            >
                <VTextField
                    v-model="formCategory.name"
                    :label="$t('category.categoryName')"
                    variant="outlined"
                    density="compact"
                    type="text"
                    :rules="nameRules"
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
                {{ $t('category.editCategory') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.link-product.modal-mask {
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
</style>
