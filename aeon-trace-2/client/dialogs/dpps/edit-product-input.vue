<script setup lang="ts">
import mimedb from 'mime-db'
import ModalLayout from '@/dialogs/modalLayout.vue'
import { b64toBlob } from '@/helpers/convert'

const { $listen } = useNuxtApp()
const { t } = useI18n()

const isEditProductInputModalOpen = ref(false)
const isInitialized = ref(false)
const productInput = ref()
const stepId = ref()
const productStepId = ref()
const productInputImge = ref()
const productInputFile = ref()
const productInputImges = ref([])
const inputsStore = useInputsStore()
const MEASUREMENT_TYPE = 'MEASUREMENT_TYPE'

const formValues = ref({
    inputs: '',
    measurementValue: '',
    updatableInput: false,
})

const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

const initializeInput = async () => {
    isInitialized.value = false

    const type = productInput.value.type.toLowerCase().replace(/\s+/g, '')

    const setValues = (value: any) => {
        formValues.value.inputs = value
        formValues.value.updatableInput = productInput.value.updatable ?? false
        formValues.value.measurementValue = productInput.value.measurementValue ?? null
    }

    switch (type) {
        case 'image':{
            const img = productInput.value.image ? `${backendUrl.value}/media/product_input_images/${productInput.value.image}` : undefined

            productInputImge.value = productInput.value.image
            setValues(img)
            break
        }

        case 'images': {
            const imgs = productInput.value.images.map((img: any) => `${backendUrl.value}/media/product_input_collection_images/${img.image}`)

            productInputImges.value = productInput.value.images.map((img: any) => `${backendUrl.value}/media/product_input_collection_images/${img.image}`)
            setValues(imgs)
            break
        }

        case 'file':
            productInputFile.value = productInput.value.document
            setValues(productInput.value.document)
            break

        case 'text':
            setValues(productInput.value.textValue)
            break

        case 'textarea':
            setValues(productInput.value.textAreaValue)
            break

        case 'numerical':
            setValues(productInput.value.numericalValue)
            break

        case 'datetime':
            setValues(productInput.value.dateTimeTo)
            break

        case 'datetimerange': {
            const range = productInput.value.dateTimeTo
                ? [productInput.value.dateTimeFrom, productInput.value.dateTimeTo]
                : [productInput.value.dateTimeFrom]

            setValues(range)
            break
        }

        case 'coordinates':
            setValues({
                lat: productInput.value.latitudeValue,
                lng: productInput.value.longitudeValue,
            })
            break

        case 'checkboxlist': {
            const inputsByStep = await inputsStore.fetchInputByStepId(stepId.value)
            const findInput = inputsByStep.find((inputByStep: any) => inputByStep.name === productInput.value.name && inputByStep.type === productInput.value.type)

            productInput.value = {
                ...productInput.value,
                options: findInput?.options ?? [],
            }

            setValues(productInput.value.checkboxValue)
            break
        }

        case 'radiolist': {
            const inputsByStep = await inputsStore.fetchInputByStepId(stepId.value)
            const findInput = inputsByStep.find((inputByStep: any) => inputByStep.name === productInput.value.name && inputByStep.type === productInput.value.type)

            productInput.value = {
                ...productInput.value,
                options: findInput?.options ?? [],
            }
            setValues(productInput.value.radioValue)
            break
        }
    }

    isInitialized.value = true
}

$listen('openEditProductInputModal', async (inputData: { productInput: any; stepId: string; productStep: string }) => {
    isEditProductInputModalOpen.value = true

    productInput.value = inputData.productInput

    stepId.value = inputData.stepId
    productStepId.value = inputData.productStep

    await initializeInput()
})

const closeEditProductInputModal = () => {
    isEditProductInputModalOpen.value = false
}

const editProductInput = async () => {
    const requestData = {
        name: productInput.value.name,
        type: productInput.value.type,
        updatable: formValues.value.updatableInput || false,
        measurementValue: Number(formValues.value.measurementValue) || 0,
    }

    const type = productInput.value.type.toLowerCase().replace(/\s+/g, '')
    let editData

    switch (type) {
        case 'image': {
            const isMeasurementType = formValues.value.inputs === MEASUREMENT_TYPE
            if (!isMeasurementType) {
                if (formValues.value?.inputs?.url) {
                    const image = b64toBlob(formValues.value?.inputs.url)
                    const imageType = mimedb[image.type]

                    const formData = new FormData()

                    formData.append('file', image, `${productInput.value.id}.${imageType.extensions[0]}`)

                    if (productInputImge.value) {
                        await useProductsInputsStore().deleteProductInputImage(productInput.value.id)
                        await useProductsInputsStore().createProductInputImage(productInput.value.id, formData)
                    } else {
                        await useProductsInputsStore().createProductInputImage(productInput.value.id, formData)
                    }
                } else {
                    await useProductsInputsStore().deleteProductInputImage(productInput.value.id)
                }
                editData = await useProductsInputsStore().fetchProductInputById(productInput.value.id)
            } else {
                editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            }
            break
        }

        case 'images': {
            const isMeasurementType = formValues.value.inputs === MEASUREMENT_TYPE
            if (!isMeasurementType) {
                const deleteImg = productInputImges.value.filter(
                    (img: string) => !formValues.value?.inputs.includes(img),
                )

                for (let i = 0; i < formValues.value?.inputs.length; i++) {
                    if (formValues.value?.inputs[i]?.startsWith('data:image')) {
                        const formData = new FormData()
                        const image = b64toBlob(formValues.value?.inputs[i])
                        const imageType = mimedb[image.type]

                        formData.append('file', image, `${productInput.value.id}.${imageType.extensions[0]}`)
                        formData.append('input', `/api/product_inputs/${productInput.value.id}`)

                        await useProductsInputsStore().createProductInputImages(formData)
                    }
                }

                if (deleteImg?.length) {
                    const productInputImages = await useProductsInputsStore().fetchProductInputImages(productInput.value.id)

                    const matchingImagesDelete = productInputImages.filter((imageObj: any) =>
                        deleteImg.some((url: any) => url.endsWith(`/${imageObj.image}`)),
                    )

                    for (let i = 0; i < matchingImagesDelete?.length; i++) {
                        const imgId = matchingImagesDelete[i].id

                        await useProductsInputsStore().deleteProductInputImages(imgId)
                    }
                }

                editData = await useProductsInputsStore().fetchProductInputById(productInput.value.id)
            } else {
                editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            }
            break
        }

        case 'file': {
            const isMeasurementType = formValues.value.inputs === MEASUREMENT_TYPE
            if (!isMeasurementType) {
                if (typeof formValues.value?.inputs !== 'string') {
                    if (productInput.value.id) {
                        await useProductsInputsStore().deleteProductInputDocument(productInput.value.id)
                    }
                    await useProductsInputsStore().createProductInputDocument(productInput.value.id, formValues.value?.inputs)
                }

                if (!formValues.value?.inputs && productInputFile.value) {
                    await useProductsInputsStore().deleteProductInputDocument(productInput.value.id)
                }
                editData = await useProductsInputsStore().fetchProductInputById(productInput.value.id)
            } else {
                editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            }
            break
        }

        case 'text':
            requestData.textValue = formValues.value?.inputs || ''
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            break

        case 'textarea':
            requestData.textAreaValue = formValues.value?.inputs || ''
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            break

        case 'numerical': {
            const isMeasurementType = formValues.value.inputs === MEASUREMENT_TYPE

            requestData.numericalValue = isMeasurementType ? 0 : Number.parseFloat(formValues.value.inputs)
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            break
        }

        case 'datetime': {
            const isMeasurementType = formValues.value.inputs === MEASUREMENT_TYPE

            const dateTimeTo = isMeasurementType
                ? null
                : formValues.value.inputs || null

            requestData.dateTimeTo = dateTimeTo
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            break
        }

        case 'datetimerange': {
            const isMeasurementType = formValues.value?.inputs === MEASUREMENT_TYPE

            const dateTimeFrom = isMeasurementType
                ? null
                : formValues.value?.inputs[0] || null

            const dateTimeTo = isMeasurementType
                ? null
                : formValues.value?.inputs[1] || null

            requestData.dateTimeFrom = dateTimeFrom
            requestData.dateTimeTo = dateTimeTo
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            break
        }

        case 'coordinates': {
            const isMeasurementType = formValues.value?.inputs === MEASUREMENT_TYPE

            const latitudeValue = isMeasurementType
                ? 0
                : formValues.value?.inputs.lat || 0

            const longitudeValue = isMeasurementType
                ? 0
                : formValues.value?.inputs.lng || 0

            requestData.latitudeValue = latitudeValue
            requestData.longitudeValue = longitudeValue
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            break
        }

        case 'checkboxlist':
            requestData.checkboxValue = Array.isArray(formValues.value.inputs) ? formValues.value.inputs : []
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            break

        case 'radiolist':
            requestData.radioValue = formValues.value?.inputs || ''
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
            break

        case 'textlist':
            editData = await useProductsInputsStore().updateProductInputById(productInput.value.id, requestData)
    }

    return editData
}

const submitEditProductInput = async () => {
    const editData = await editProductInput()

    useNuxtApp().$event('message', {
        type: 'success',
        message: t('messages.productInput'),
        title: 'Success',
    })

    useNuxtApp().$event('editProductInputUpdate', { success: true, editData, productStepId: productStepId.value })

    closeEditProductInputModal()
}
</script>

<template>
    <ModalLayout
        :is-open="isEditProductInputModalOpen"
        name="edit-product-input-modal"
        :title="$t('products.editInputs')"
        button-submit-text="Save"
        class="edit-product-input"
        @modal-close="closeEditProductInputModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="input-by-type">
                <InputByType
                    v-if="isInitialized"
                    v-model="formValues.inputs"
                    :measurement-model-value="formValues.measurementValue"
                    :updatable-input="formValues.updatableInput"
                    :type="productInput.type"
                    :name="productInput.name"
                    :input="productInput"
                    :data="formValues.inputs"
                    :show-updatable-input="false"
                    @update:measurement-model-value="val => formValues.measurementValue = val"
                    @update:updatable-input="val => formValues.updatableInput = val"
                />
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                @click="submitEditProductInput"
            >
                {{ $t('products.editInputs') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.edit-product-input.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        .modal-body {
            .input-by-type {
                padding: 2rem;
            }
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
