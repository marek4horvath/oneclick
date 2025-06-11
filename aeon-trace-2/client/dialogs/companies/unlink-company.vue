<script setup lang="ts">
import { uniqBy } from 'lodash'
import type { SelectItem } from '@/types/selectItem'
import ModalLayout from '@/dialogs/modalLayout.vue'

const props = defineProps({
    companyId: {
        type: String,
        required: false,
    },
})

const hasPrefilledCompany = computed(() => !!props.companyId)

const { $listen } = useNuxtApp()

const productsStore = useProductsStore()

const isUnlinkCompanyModalOpen = ref(false)
const form = ref(null)

const companiesItems = ref<SelectItem[]>([])

const formProductUnlinkCompany = ref({
    companies: [],
})

const productId = ref('')

const isLoading = ref({
    company: true,
})

const companyRules = [
    (v: string) => !!v || 'Company is required',
]

const valid = ref(false)

$listen('openUnlinkCompanyModal', async (data: any) => {
    isUnlinkCompanyModalOpen.value = true

    if (!data.companies || !data.productId) {
        return
    }

    companiesItems.value = data.companies.map((company: any) => {
        return {
            value: company.id,
            title: company.name,
        }
    })

    productId.value = data.productId

    isLoading.value.company = false
})

const closeUnlinkCompanyModal = () => {
    isUnlinkCompanyModalOpen.value = false
    isLoading.value.company = false

    formProductUnlinkCompany.value = {
        companies: [],
    }
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const companies = formProductUnlinkCompany.value.companies.map((company: string) => `/api/companies/${company}`)

    const editProductResponse = await productsStore.updateProductUnlinkCompany(productId.value, companies)

    if (!editProductResponse) {
        return
    }

    closeUnlinkCompanyModal()
    useNuxtApp().$event('productDetail', { success: true, product: editProductResponse })
}
</script>

<template>
    <ModalLayout
        :is-open="isUnlinkCompanyModalOpen"
        name="link-product-modal"
        :title="$t('products.unlinkCompany')"
        button-submit-text="Save"
        class="link-product"
        @modal-close="closeUnlinkCompanyModal"
        @submit="submitHandler"
    >
        <template #content>
            <VForm
                ref="form"
                v-model="valid"
            >
                <VSelect
                    v-model="formProductUnlinkCompany.companies"
                    :label="$t('products.company')"
                    :placeholder="$t('selectCompany')"
                    :items="uniqBy(companiesItems, 'value')"
                    :rules="companyRules"
                    variant="outlined"
                    :loading="isLoading.company"
                    :no-data-text="isLoading.company ? $t('products.loadingCompanies') : $t('noDataAvailable')"
                    :disabled="hasPrefilledCompany"
                    multiple
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
                {{ $t('products.unlinkCompany') }}
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
