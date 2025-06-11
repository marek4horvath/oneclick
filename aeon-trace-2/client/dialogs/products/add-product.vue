<script setup lang="ts">
import mimedb from 'mime-db'
import { b64toBlob } from '@/helpers/convert'
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen, $alert } = useNuxtApp()

const isAddProductModalOpen = ref(false)
const autoCreateStep = ref<boolean>(false)
const newImageFile = ref<string>()
const productsStore = useProductsStore()
const processStore = useProcessStore()
const stepsTemplateStore = useStepsTemplateStore()
const stepsStore = useStepsStore()
const form = ref(null)
const companyId = ref(null)

const formProduct = ref({
    name: '',
    description: '',
})

const valid = ref(false)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

$listen('openAddProductModal', (idCompany?: string) => {
    isAddProductModalOpen.value = true
    companyId.value = idCompany
    formProduct.value.name = ''
    formProduct.value.description = ''
})

const closeAddProductModal = () => {
    isAddProductModalOpen.value = false
}

const imageChanged = (image: string) => {
    newImageFile.value = image
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const responseProduct = await productsStore.createProduct({
        name: formProduct.value.name,
        description: formProduct.value.description,
        companies: companyId.value ? [`/api/companies/${companyId.value}`] : [],
    })

    if (!responseProduct) {
        return
    }

    if (newImageFile.value) {
        const image = b64toBlob(newImageFile.value.url)

        const imageType = mimedb[image.type]

        const formData = new FormData()

        formData.append('file', image, `${responseProduct.id}.${imageType.extensions[0]}`)
        await productsStore.uploadProductImage(responseProduct.id, formData)
    }

    if (!autoCreateStep.value) {
        navigateTo(`/products/detail/${responseProduct.id}`)
        isAddProductModalOpen.value = false

        return
    }

    const idProduct = responseProduct.id
    const nameProduct = responseProduct.name

    const addStepTemplateResponse = await stepsTemplateStore.createStepTemplate({
        name: `${nameProduct}StepTemplate`,
    })

    if (!addStepTemplateResponse) {
        return
    }

    const editProductResponse = await productsStore.updateProduct(idProduct,
        {
            stepsTemplate: `/api/steps_templates/${addStepTemplateResponse.id}`,
        },
    )

    if (!editProductResponse) {
        $alert('Product was not created', 'Error', 'error')

        return
    }

    const processIri = processStore.getProcesses.find((process: any) => process.name === 'Raw material collection')

    if (processIri === undefined) {
        $alert('Step was not created', 'Error', 'error')

        navigateTo(`/products/detail/${responseProduct.id}`)
        isAddProductModalOpen.value = false

        return
    }

    const addStepResponse = await stepsStore.createStep(
        {
            name: nameProduct,
            stepsTemplate: `/api/steps_templates/${addStepTemplateResponse.id}`,
            parentStep: null,
            productTemplate: `/api/product_templates/${idProduct}`,
            batchTypeOfStep: 'BATCH',
            process: `/api/processes/${processIri.id}`,
            quantity: 1,
            sort: 0,
            createQr: false,
        },
    )

    if (!addStepResponse) {
        $alert('Step was not created', 'Error', 'error')
        navigateTo(`/products/detail/${responseProduct.id}`)
        isAddProductModalOpen.value = false

        return
    }

    navigateTo(`/products/detail/${responseProduct.id}`)
    isAddProductModalOpen.value = false
}
</script>

<template>
    <ModalLayout
        :is-open="isAddProductModalOpen"
        name="add-product-modal"
        title="Add product"
        button-submit-text="Save"
        class="add-product"
        @modal-close="closeAddProductModal"
        @submit="submitHandler"
    >
        <template #content>
            <VForm
                ref="form"
                v-model="valid"
            >
                <VTextField
                    v-model="formProduct.name"
                    :label="$t('products.name')"
                    variant="outlined"
                    density="compact"
                    type="text"
                    :rules="nameRules"
                />

                <VTextarea
                    v-model="formProduct.description"
                    :label="$t('products.description')"
                    variant="outlined"
                />

                <ImageUploadComponent @image-changed="imageChanged" />

                <VCheckbox
                    v-model="autoCreateStep"
                    :label="$t('products.createStepAuto')"
                    color="primary"
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
                {{ $t('products.addProduct') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.add-product.modal-mask {
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
