<script setup lang="ts">
import { uniqBy } from 'lodash'
import ModalLayout from '@/dialogs/modalLayout.vue'
import type { SelectItem } from '~/types/selectItem.ts'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()

const isModalOpen = ref(false)
const form = ref(null)
const isEditMode = ref(false)

const inputTypeItems = ref<SelectItem[]>([
    { title: t('inputType.text'), value: 'text' },
    { title: t('inputType.textArea'), value: 'textarea' },
    { title: t('inputType.selectImage'), value: 'image' },
    { title: t('inputType.selectImages'), value: 'images' },
    { title: t('inputType.selectFile'), value: 'file' },
    { title: t('inputType.selectNumerical'), value: 'numerical' },
    { title: t('inputType.selectCoordinates'), value: 'coordinates' },
    { title: t('inputType.selectDateTime'), value: 'dateTime' },
    { title: t('inputType.selectDateTimeRange'), value: 'dateTimeRange' },
])

const inputForm = ref({
    id: '',
    name: '',
    type: '',
})

const inputNameRules = [
    (v: string) => !!v || 'Input name is required',
    (v: string) => v.trim().length > 0 || 'Input Type cannot be empty',
]

const inputTypeRules = [
    (v: string) => !!v || 'Input type is required',
    (v: string) => v.trim().length > 0 || 'Input type cannot be empty',
]

const valid = ref(false)

$listen('openInputModal', async (item?: any) => {
    if (item?.name) {
        isEditMode.value = true
        inputForm.value = {
            id: item.id,
            name: item.name,
            type: item.type,
        }
    } else {
        isEditMode.value = false
        inputForm.value = {
            id: '',
            name: '',
            type: '',
        }
    }
    isModalOpen.value = true
})

const closeAddProductModal = () => {
    isModalOpen.value = false
    isEditMode.value = false

    inputForm.value = {
        id: '',
        name: '',
        type: '',
    }
}

const submitHandler = async () => {
    $event('handleInputChanges', inputForm.value)
    isModalOpen.value = false
    isEditMode.value = false

    inputForm.value = {
        id: '',
        name: '',
        type: '',
    }

    closeAddProductModal()
}
</script>

<template>
    <ModalLayout
        :is-open="isModalOpen"
        name="link-product-modal"
        :title="isEditMode ? t('input.editInput') : t('input.addInput')"
        button-submit-text="Save"
        class="link-product"
        @modal-close="closeAddProductModal"
    >
        <template #content>
            <VForm
                ref="form"
                v-model="valid"
            >
                <VTextField
                    v-model="inputForm.name"
                    :label="t('input.name')"
                    variant="outlined"
                    density="compact"
                    type="text"
                    :rules="inputNameRules"
                />
                <VSelect
                    v-model="inputForm.type"
                    :label="t('input.type')"
                    :items="uniqBy(inputTypeItems, 'value')"
                    class="custom-select"
                    :rules="inputTypeRules"
                    variant="outlined"
                />
            </VForm>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                :disabled="!valid"
                @click="submitHandler"
            >
                {{ isEditMode ? t('input.editInputCaps') : t('input.addInputCaps') }}
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
                    height: 48px;

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
