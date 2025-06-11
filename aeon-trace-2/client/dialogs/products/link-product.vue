<script setup lang="ts">
import { uniqBy } from 'lodash'
import type { SelectItem } from '@/types/selectItem'
import ModalLayout from '@/dialogs/modalLayout.vue'
import type { MessageBag } from '@/types/index'

const props = defineProps({
    companyId: {
        type: String,
        required: false,
    },
})

const hasPrefilledCompany = computed(() => !!props.companyId)

const { $event, $listen } = useNuxtApp()

const productsStore = useProductsStore()
const companiesStore = useCompaniesStore()

const isLinkProductModalOpen = ref(false)
const form = ref(null)

const companiesItems = ref<SelectItem[]>([])
const productsItems = ref<SelectItem[]>([])
const { t } = useI18n()

const formProductLinkCompany = ref({
    product: '',
    company: '',
})

const isLoading = ref({
    product: true,
    company: true,
})

const processRules = [
    (v: string) => !!v || 'Product is required',
]

const companyRules = [
    (v: string) => !!v || 'Company is required',
]

const valid = ref(false)

function mapCompanyToSelectItem(company: any) {
    return {
        value: company.id,
        title: company.name || `Company not name ${company.id}`,
    }
}

function mapProductToSelectItem(product: any) {
    return {
        value: product.id,
        title: product.name,
    }
}

$listen('openLinkProductModal', async (products?: any[]) => {
    isLinkProductModalOpen.value = true

    const passedProductIds = new Set((products || []).map(p => p.id))
    let availableProducts: any[]

    // NOTE: Must be a listing fetch to display companies ids field in payload
    await productsStore.fetchProductsListing(1, 200)

    if (props.companyId) {
        // Fetch and filter products
        availableProducts = productsStore.getProducts.filter(p => !passedProductIds.has(p.id))
        isLoading.value.product = false

        // Get Company - Cache or BE fetch
        let company = companiesStore.getCompanyById(props.companyId)
        if (!company) {
            company = await companiesStore.fetchCompanyById(props.companyId)
        }

        if (company) {
            companiesItems.value = [mapCompanyToSelectItem(company)]
            formProductLinkCompany.value.company = company.id
            isLoading.value.company = false
        }
    } else {
        await companiesStore.fetchCompaniesListing(undefined, undefined, undefined, false, true)

        availableProducts = products || productsStore.getProducts
        isLoading.value.product = false
        companiesItems.value = companiesStore.getCompanies.map(mapCompanyToSelectItem)
        isLoading.value.company = false
    }

    productsItems.value = availableProducts.map(mapProductToSelectItem)
})

const handleLinkProductSubmitted = () => {
    $event('handleLinkProductSubmitted')
}

const closeLinkProductModal = () => {
    isLinkProductModalOpen.value = false

    formProductLinkCompany.value = {
        product: '',
        company: '',
    }
}

const submitHandler = async () => {
    const formValidation: any = form.value
    const companiesData: string[] = []

    formValidation.validate()

    if (!valid.value) {
        return
    }

    if (productsStore.products) {
        productsStore.products.forEach((product: any) => {
            if (product.id === formProductLinkCompany.value.product) {
                const companyIdsArray = product.companiesIds?.split(',')

                companyIdsArray.forEach((companyId: any) => {
                    const id = companyId.trim()

                    if (id !== '') {
                        companiesData.push(`/api/companies/${id}`)
                    }
                })
            }
        })
    }

    if (companiesData.includes(`/api/companies/${formProductLinkCompany.value.company}`)) {
        const message: MessageBag = {
            type: 'warning',
            message: t('messages.warningLinkProduct'),
            title: 'Warning',
        }

        useNuxtApp().$event('message', message)

        return
    }

    companiesData.push(`/api/companies/${formProductLinkCompany.value.company}`)

    const editProductResponse = await productsStore.updateProduct(formProductLinkCompany.value.product, {
        companies: companiesData.filter((cd: string) => cd !== '/api/companies/'),
    })

    if (!editProductResponse) {
        return
    }

    handleLinkProductSubmitted()
    isLinkProductModalOpen.value = false

    formProductLinkCompany.value = {
        product: '',
        company: '',
    }
}
</script>

<template>
    <ModalLayout
        :is-open="isLinkProductModalOpen"
        name="link-product-modal"
        title="Link product"
        button-submit-text="Save"
        class="link-product"
        @modal-close="closeLinkProductModal"
        @submit="submitHandler"
    >
        <template #content>
            <VForm
                ref="form"
                v-model="valid"
            >
                <VSelect
                    v-model="formProductLinkCompany.company"
                    :label="$t('products.company')"
                    :placeholder="$t('selectCompany')"
                    :items="uniqBy(companiesItems, 'value')"
                    :rules="companyRules"
                    variant="outlined"
                    :loading="isLoading.company"
                    :no-data-text="isLoading.company ? $t('products.loadingCompanies') : $t('noDataAvailable')"
                    :disabled="hasPrefilledCompany"
                />

                <VSelect
                    v-model="formProductLinkCompany.product"
                    :label="$t('products.product')"
                    :placeholder="$t('selectProduct')"
                    :items="uniqBy(productsItems, 'value')"
                    :rules="processRules"
                    variant="outlined"
                    :loading="isLoading.product"
                    :no-data-text="isLoading.product ? $t('products.loadingProducts') : $t('noDataAvailable')"
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
                {{ $t('products.linkProduct') }}
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

.v-overlay-container {
    z-index: 9999 !important;
}
</style>
