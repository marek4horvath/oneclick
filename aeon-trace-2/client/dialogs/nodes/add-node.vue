<script setup lang="ts">
import { uniqBy } from 'lodash'
import type { SelectItem } from '@/types/selectItem'
import ModalLayout from '@/dialogs/modalLayout.vue'
import type { Product } from '~/types/api/product'

const { $event, $listen } = useNuxtApp()

const isAddNodeModalOpen = ref(false)
const form = ref(null)
const processStore = useProcessStore()
const productsStore = useProductsStore()
const nodeStore = useNodesStore()

const formNode = ref({
    id: '',
    name: '',
    description: '',
    process: null,
    nextNode: null,
    previousNode: null,
    product: null,
    templateNode: null,
})

const selectedProducts = ref([])
const selectedNextNode = ref([null])
const selectedPreviousNode = ref([null])

const processItems = ref<SelectItem[]>([])
const nodeItems = ref<SelectItem[]>([])
const nodeTemplates = ref<SelectItem[]>([])
const productSteps = ref([])
const productTemplates = ref([])
const nodeTemplateIds = new Set<string>()

const isLoading = ref({
    products: true,
    nodeTemplate: true,
})

const supplyChain = ref()

const valid = ref(false)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v?.trim()?.length > 0 || 'Name cannot be empty',
]

const processRules = [
    (v: string) => !!v || 'Process is required',
]

const fetchProcess = async () => {
    await processStore.fetchProcesses(undefined, undefined, 'node')

    const process = processStore.getProcessesByType('node')

    processItems.value = []

    process.forEach((processItem: any) => {
        processItems.value.push({
            value: processItem.id,
            title: processItem.name,
        })
    })

    processItems.value = processItems.value.filter(
        (item: any, index: any, self: any) =>
            index === self.findIndex((currentItem: any) => currentItem.value === item.value),
    )
}

const fetchNodes = () => {
    if (!supplyChain.value?.nodes) {
        return
    }

    supplyChain.value.nodes.forEach((node: any) => {
        if (nodeItems.value.find((item: any) => item.value === node.id)) {
            return
        }

        nodeItems.value.push({
            value: node.id,
            title: node.name,
        })
    })
}

const fetchTemplates = async () => {
    if (!supplyChain.value?.productTemplates) {
        return
    }

    const localProductTemplates: any = []

    supplyChain.value.productTemplates.forEach(async (template: any) => {
        const templateId = template.split('/').pop()
        let storedTemplate = productsStore.fetchProductById(templateId)

        if (!storedTemplate) {
            storedTemplate = await productsStore.fetchProductById(templateId)
        }

        localProductTemplates.push({
            value: storedTemplate.id,
            title: storedTemplate.name,
        })
    })
    productTemplates.value = uniqBy(localProductTemplates, 'value')
    isLoading.value.products = false
}

const fetchNodeTemplates = async (nodeTemplatesIds: Array<string>) => {
    for (const nodeTemplateItem of nodeTemplatesIds) {
        const nodeTemplateId: any = nodeTemplateItem.split('/').pop()
        if (nodeTemplateIds.has(nodeTemplateId)) {
            continue
        }

        const getProductResponse = await productsStore.fetchProductById(nodeTemplateId)

        if (!getProductResponse) {
            continue
        }

        nodeTemplateIds.add(getProductResponse.id)

        if (nodeTemplates.value.length > 0) {
            nodeTemplates.value.unshift({
                value: getProductResponse.id,
                title: getProductResponse.name,
            })
        } else {
            nodeTemplates.value.push({
                value: getProductResponse.id,
                title: getProductResponse.name,
            })
        }
    }

    isLoading.value.nodeTemplate = false
}

$listen('openAddNodeModal', async (data: string | any) => {
    isAddNodeModalOpen.value = true

    nodeItems.value = []
    selectedNextNode.value = [null]
    selectedPreviousNode.value = [null]

    formNode.value = {
        id: '',
        name: '',
        description: '',
        process: null,
        nextNode: null,
        previousNode: null,
        product: null,
        steps: [],
        templateNode: null,
    }

    supplyChain.value = data
    await fetchNodeTemplates(data.nodeTemplates)
    await fetchProcess()
    await fetchTemplates()
    fetchNodes()
})

$listen('handleNodeTemplateAddSubmitted', (nodeTemplate: any) => {
    if (!nodeTemplate) {
        return
    }

    const exists = nodeTemplates.value.some((item: any) => item.value === nodeTemplate.id)

    if (exists) {
        return
    }

    nodeTemplates.value.push({
        value: nodeTemplate.id,
        title: nodeTemplate.name,
    })
})

const closeAddNodeModal = () => {
    isAddNodeModalOpen.value = false
}

const products = computed(() => {
    return productsStore.products
        .map((product: Product) => {
            return supplyChain.value.productTemplates.includes(`/api/product_templates/${product.id}`)
                ? { value: product.id, title: product.name }
                : undefined
        })
        .filter(Boolean)
})

const handleNodeSubmitted = () => {
    $event('handleNodeSubmitted')
}

watch(() => selectedProducts.value, () => {
    productSteps.value = uniqBy(productsStore.products
        .filter((product: Product) => selectedProducts.value.includes(product.id))
        .map((product: Product) => {
            const steps = []

            for (const step of product.stepsTemplate?.steps ?? []) {
                steps.push({
                    value: step.id,
                    title: step.name,
                })
            }

            return steps
        }).flat(), 'value')
})

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const nodeResponse = await nodeStore.createNode({
        name: formNode.value.name,
        description: formNode.value.description,
        typeOfProcess: `/api/processes/${formNode.value.process}`,
        children: selectedNextNode.value?.map((id: string) => {
            return id !== null ? `/api/nodes/${id}` : undefined
        }).filter(Boolean),
        parents: selectedPreviousNode.value?.map((id: string) => {
            return id !== null ? `/api/nodes/${id}` : undefined
        }).filter(Boolean),
        productTemplates: selectedProducts.value.length > 0 ? selectedProducts.value.map((id: string) => `/api/product_templates/${id}`) : [],
        steps: formNode.value.steps?.map((id: string) => {
            return id !== null ? `/api/steps/${id}` : undefined
        }).filter(Boolean),
        nodeTemplate: formNode.value.templateNode ? `/api/product_templates/${formNode.value.templateNode}` : null,
        supplyChainTemplate: supplyChain.value?.id ? `/api/supply_chain_templates/${supplyChain.value.id}` : null,
    })

    if (!nodeResponse) {
        return
    }

    handleNodeSubmitted()
    isAddNodeModalOpen.value = false
}

// Remove NULL when something else is selected and add null when nothing is selected
watch(() => selectedPreviousNode.value, (newVal: Array<string | null>, oldVal: Array<string | null>) => {
    if (newVal.includes(null) && oldVal?.includes(null) === false) {
        selectedPreviousNode.value = [null]
        formNode.value.previousNode = [null]

        return
    }

    if (JSON.stringify(newVal) === JSON.stringify(oldVal)) {
        return
    }

    const nonNullValues = newVal.filter(id => id !== null)

    if (nonNullValues.length === 0) {
        selectedPreviousNode.value = [null]
        formNode.value.previousNode = [null]
    } else {
        selectedPreviousNode.value = nonNullValues
        formNode.value.previousNode = nonNullValues
    }
}, { deep: true })

watch(() => selectedNextNode.value, (newVal: Array<string | null>, oldVal: Array<string | null>) => {
    if (newVal.includes(null) && oldVal?.includes(null) === false) {
        selectedNextNode.value = [null]
        formNode.value.nextNode = [null]

        return
    }

    if (JSON.stringify(newVal) === JSON.stringify(oldVal)) {
        return
    }

    const nonNullValues = newVal.filter(id => id !== null)

    if (nonNullValues.length === 0) {
        selectedNextNode.value = [null]
        formNode.value.nextNode = [null]
    } else {
        selectedNextNode.value = nonNullValues
        formNode.value.nextNode = nonNullValues
    }
}, { deep: true })
</script>

<template>
    <ModalLayout
        :is-open="isAddNodeModalOpen"
        name="add-node-modal"
        :title="$t('supplyChains.addNode')"
        button-submit-text="Save"
        class="add-node"
        @modal-close="closeAddNodeModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="form-wrapper">
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VTextField
                        v-model="formNode.name"
                        :label="$t('supplyChains.node.name')"
                        variant="outlined"
                        density="compact"
                        type="text"
                        :rules="nameRules"
                    />

                    <VTextarea
                        v-model="formNode.description"
                        :label="$t('supplyChains.node.description')"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="formNode.process"
                        :label="$t('products.selectProcess')"
                        :items="uniqBy(processItems, 'value')"
                        :rules="processRules"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="selectedPreviousNode"
                        :items="[{ value: null, title: $t('supplyChains.node.noPreviousNode') }, ...uniqBy(nodeItems, 'value')]"
                        multiple
                        :label="$t('supplyChains.node.previous')"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="selectedNextNode"
                        :items="[{ value: null, title: $t('supplyChains.node.noNextNode') }, ...uniqBy(nodeItems, 'value')]"
                        multiple
                        :label="$t('supplyChains.node.next')"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="selectedProducts"
                        :items="uniqBy(products, 'value')"
                        multiple
                        :label="$t('supplyChains.node.product')"
                        variant="outlined"
                        :loading="isLoading.products"
                        :no-data-text="isLoading.products ? $t('supplyChains.loadingProducts') : $t('noDataAvailable')"
                    />

                    <VSelect
                        v-if="selectedProducts?.length > 0 || false"
                        v-model="formNode.steps"
                        :items="uniqBy(productSteps, 'value')"
                        multiple
                        :label="$t('supplyChains.node.steps')"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="formNode.templateNode"
                        :items="nodeTemplates"
                        :label="$t('supplyChains.node.template')"
                        variant="outlined"
                        :loading="isLoading.nodeTemplate"
                        :no-data-text="isLoading.nodeTemplate ? $t('supplyChains.loadingTemplates') : $t('noDataAvailable')"
                    />
                </VForm>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                height="45"
                @click="submitHandler"
            >
                {{ $t('supplyChains.addNode') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.add-node.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        .modal-body {
            .form-wrapper {
                height: 400px;
                padding-top: 1rem;
                overflow-y: scroll;

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
