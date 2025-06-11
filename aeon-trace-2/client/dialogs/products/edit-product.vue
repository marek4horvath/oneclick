<script setup lang="ts">
import mimedb from 'mime-db'
import { b64toBlob } from '@/helpers/convert'
import ModalLayout from '@/dialogs/modalLayout.vue'
import { checkImage } from '@/helpers/imageCheck'

const { $event, $listen } = useNuxtApp()

const isEditProductModalOpen = ref(false)
const productsStore = useProductsStore()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const form = ref(null)

const formProduct = ref({
    id: '',
    name: '',
    description: '',
    image: '',
})

const productData = ref()
const valid = ref(false)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

$listen('openEditProductModal', product => {
    productData.value = product

    const imageUrl = productData.value.productImage
        ? `${backendUrl.value}/media/product_images/${productData.value.productImage}`
        : undefined

    if (imageUrl) {
        checkImage(imageUrl)
            .then((result: boolean) => {
                if (!result) {
                    formProduct.value.image = undefined

                    return
                }

                formProduct.value.image = imageUrl
            })
    }

    formProduct.value = {
        id: productData.value.id,
        name: productData.value.name,
        description: productData.value.description,
    }
    isEditProductModalOpen.value = true
})

const closeEditProductModal = () => {
    isEditProductModalOpen.value = false
}

const onImageUploaded = (image: string) => {
    form.value.image = image
}

const handleProductSubmitted = () => {
    $event('handleProductSubmitted')
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const editProductResponse = await productsStore.updateProduct(formProduct.value.id, formProduct.value)

    if (!editProductResponse) {
        return
    }

    if (form.value.image) {
        const urlPattern = new RegExp('^(https?:\\/\\/)?'
        + '((([a-zA-Z0-9$-_@.&+!*\'(),]+)\\.[a-zA-Z]{2,})|'
        + '((\\d{1,3}\\.){3}\\d{1,3}))'
        + '(\\:\\d+)?(\\/[-a-zA-Z0-9@:%_+.~#?&//=]*)?'
        + '(\\?[;&a-zA-Z0-9%_.~+=-]*)?'
        + '(\\#[-a-zA-Z0-9_]*)?$', 'i')

        if (urlPattern.test(form.value.image)) {
            return
        }

        const image = b64toBlob(form.value.image.url)

        const imageType = mimedb[image.type]

        const formData = new FormData()

        formData.append('file', image, `${formProduct.value.id}.${imageType.extensions[0]}`)

        await productsStore.uploadProductImage(formProduct.value.id, formData)
    } else {
        await productsStore.deleteProductImage(formProduct.value.id)
    }

    handleProductSubmitted()
    isEditProductModalOpen.value = false
}
</script>

<template>
    <ModalLayout
        :is-open="isEditProductModalOpen"
        name="edit-product-modal"
        title="Edit product"
        button-submit-text="Save"
        class="edit-product"
        @modal-close="closeEditProductModal"
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
                <ImageUploadComponent
                    :image="formProduct.image || undefined"
                    @image-changed="onImageUploaded"
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
                {{ $t('products.editProduct') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.edit-product.modal-mask {
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
