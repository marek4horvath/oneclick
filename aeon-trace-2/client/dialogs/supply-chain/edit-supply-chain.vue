<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $event, $listen, $alert } = useNuxtApp()
const productsStore = useProductsStore()
const supplyChainStore = useSupplyChainStore()

const isEditSupplyChainModalOpen = ref(false)
const idSupply = ref('')
const name = ref('')
const products = ref([])
const templates = ref([])
const valid = ref(false)
const form = ref(null)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.length > 0 || 'Name cannot be empty',
]

const productsRules = [
    (v: string) => !!v || 'Products are required',
    (v: string) => v.length > 0 || 'Products cannot be empty',
]

$listen('editSupplyChain', async (supplyChain: any) => {
    isEditSupplyChainModalOpen.value = true

    templates.value = await productsStore.fetchProductsListing()
    idSupply.value = supplyChain.id
    name.value = supplyChain.name
    products.value = supplyChain.productTemplates.map((productTemplate: any) => productTemplate.split('/').pop())
})

const closeEditSupplyChainModal = () => {
    $event('updateSupplyChainTableAfterEdit')

    isEditSupplyChainModalOpen.value = false
}

const handleSupplyChainSubmitted = () => {
    $event('handleSupplyChainSubmitted')
}

const submitHandler = async () => {
    valid.value = form.value?.validate()

    if (!valid.value) {
        return
    }

    const productTemplates = products.value.map((product: any) => {
        return `/api/product_templates/${product}`
    })

    const response = await supplyChainStore.updateSupplyChainTemplate(idSupply.value, {
        name: name.value,
        productTemplates,
    })

    if (!response) {
        return
    }

    $alert('Supply chain edited', 'Success', 'success')
    handleSupplyChainSubmitted()
    closeEditSupplyChainModal()
}
</script>

<template>
    <ModalLayout
        :is-open="isEditSupplyChainModalOpen"
        name="edit-supply-chain-modal"
        title="Edit Supply Chain"
        button-cancel-text="Cancel"
        button-submit-text="Edit"
        disable-cancel-button
        @modal-close="closeEditSupplyChainModal"
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
                />
            </VForm>
        </template>
    </ModalLayout>
</template>
