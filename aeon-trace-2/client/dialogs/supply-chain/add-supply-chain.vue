<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen, $alert } = useNuxtApp()

const productTemplatesStore = useProductsStore()
const supplyChainStore = useSupplyChainStore()

const isAddSupplyChainModalOpen = ref(false)
const name = ref('')
const products = ref([])
const templates = ref([])
const form = ref(null)

const isLoading = ref({
    products: true,
})

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.length > 0 || 'Name cannot be empty',
]

const productsRules = [
    (v: string) => !!v || 'Products are required',
    (v: string) => v.length > 0 || 'Products cannot be empty',
]

$listen('addSupplyChain', () => {
    isAddSupplyChainModalOpen.value = true
})

const closeAddSupplyChainModal = () => {
    isAddSupplyChainModalOpen.value = false
}

const submitHandler = async () => {
    const formValidation = await form.value.validate()

    if (!formValidation.valid) {
        return
    }

    const response = await supplyChainStore.createSupplyChain(name.value, products.value)

    $alert('Supply chain created', 'Success', 'success')
    closeAddSupplyChainModal()
    navigateTo(`/supply-chains/detail/${response.id}`)
}

watch(() => isAddSupplyChainModalOpen.value, async newValue => {
    if (!newValue) {
        return
    }

    templates.value = await productTemplatesStore.fetchProductsListing(1, 100)

    isLoading.value.products = false
})
</script>

<template>
    <ModalLayout
        :is-open="isAddSupplyChainModalOpen"
        name="add-supply-chain-modal"
        title="Add Supply Chain"
        button-cancel-text="Cancel"
        button-submit-text="Create"
        disable-cancel-button
        @modal-close="closeAddSupplyChainModal"
        @submit="submitHandler"
    >
        <template #description>
            To create a supply chain please provide the name<br>
            and click on the button bellow
        </template>

        <template #content>
            <VForm
                ref="form"
                class="mt-8"
            >
                <VTextField
                    v-model="name"
                    label="Name"
                    variant="outlined"
                    type="text"
                    :rules="nameRules"
                />

                <VSelect
                    v-model="products"
                    chips
                    multiple
                    clearable
                    label="Products"
                    variant="outlined"
                    item-title="name"
                    item-value="id"
                    :items="templates"
                    :rules="productsRules"
                    :loading="isLoading.products"
                    :no-data-text="isLoading.products ? $t('supplyChains.loadingProducts') : $t('noDataAvailable')"
                />
            </VForm>
        </template>
    </ModalLayout>
</template>
