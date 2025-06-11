<script setup lang="ts">
import { uniqBy } from 'lodash'
import type { SelectItem } from '@/types/selectItem'
import ModalLayout from '@/dialogs/modalLayout.vue'
import type { Product } from '~/types/api/product'

const { $event, $listen } = useNuxtApp()

const isEditNodeModalOpen = ref(false)
const form = ref(null)
const processStore = useProcessStore()
const productsStore = useProductsStore()
const nodeStore = useNodesStore()

const formNode = ref({
    id: '',
    name: '',
    description: '',
    process: {
        title: '',
        value: '',
    },
    nextNode: null,
    previousNode: null,
    product: null,
    template: null,
    steps: [],
})

const selectedProducts = ref([])
const selectedNextNode = ref([null])
const selectedPreviousNode = ref([null])
const selectedSteps = ref([])

const processItems = ref<SelectItem[]>([])
const nodeItems = ref<SelectItem[]>([])
const nodeTemplates = ref<SelectItem[]>([])
const productSteps = ref([])
const productTemplates = ref([])
const productTemplatesData = ref([])

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

const fetchProductTemplates = async () => {
    if (!supplyChain.value?.productTemplates) {
        return
    }

    const localProductTemplatesIds: any = []

    for (const template of supplyChain.value.productTemplates) {
        const templateId = template.split('/').pop()

        localProductTemplatesIds.push(templateId)
    }

    if (!localProductTemplatesIds?.length) {
        return
    }

    productTemplatesData.value = await productsStore.fetchProductByIds(localProductTemplatesIds)

    if (!productTemplatesData.value || !productTemplatesData.value?.product_templates) {
        return
    }

    productTemplatesData.value.product_templates.forEach((productTemplate: any) => {
        productTemplates.value.push({
            value: productTemplate.id,
            title: productTemplate.name,
        })
    })
}

const fetchNodeTemplates = async () => {
    if (!supplyChain.value?.nodeTemplates) {
        return
    }

    const localNodeTemplatesIds: any = []

    for (const template of supplyChain.value.nodeTemplates) {
        const templateId = template.split('/').pop()

        localNodeTemplatesIds.push(templateId)
    }

    if (!localNodeTemplatesIds?.length) {
        return
    }

    const productTemplatesNode = await productsStore.fetchProductByIds(localNodeTemplatesIds)

    if (!productTemplatesNode || !productTemplatesNode?.product_templates) {
        return
    }

    productTemplatesNode.product_templates.forEach((productTemplate: any) => {
        nodeTemplates.value.push({
            value: productTemplate.id,
            title: productTemplate.name,
        })
    })
}

onMounted(() => {
    const handleOpenEditNodeModal = async (data: any) => {
        isEditNodeModalOpen.value = true

        nodeItems.value = []
        nodeTemplates.value = []

        supplyChain.value = data.supplyChain

        formNode.value = {
            id: data.node.id,
            name: data.node.name,
            description: data.node.description,
            process: data.node.typeOfProcess
                ? {
                    title: data.node.typeOfProcess?.name || '',
                    value: data.node.typeOfProcess?.id || '',
                }
                : { title: '', value: '' },
            template: data.node.nodeTemplate ? data.node.nodeTemplate?.split('/')?.pop() : null,
            steps: data.node.steps ? data.node.steps.map((step: any) => step.id) : [],
        }

        await fetchProcess()
        await fetchProductTemplates()
        await fetchNodeTemplates()
        fetchNodes()

        selectedNextNode.value = data.node.children
            ? data.node.children.map((child: any) => {
                const childId = child?.id ? child.id.split('/').pop() : child.split('/').pop()

                return childId
            })
            : []

        selectedPreviousNode.value = data.node.parents
            ? data.node.parents.map((parent: any) => {
                const parentId = parent?.id ? parent.id.split('/').pop() : parent.split('/').pop()

                return parentId
            })
            : []

        selectedProducts.value = data.node.productTemplates
            ? data.node.productTemplates.map((p: any) => p.split('/').pop())
            : []

        selectedSteps.value = data.node.steps
            ? data.node.steps.map((step: any) => step.id.split('/').pop())
            : []
    }

    $listen('openEditNodeModal', handleOpenEditNodeModal)

    onUnmounted(() => {
        if ($event && typeof $event.off === 'function') {
            $event.off('openEditNodeModal', handleOpenEditNodeModal)
        }
    })
})

const closeAddNodeModal = () => {
    isEditNodeModalOpen.value = false
}

watch(() => selectedProducts.value, () => {
    if (!productTemplatesData.value?.product_templates) {
        return
    }

    productSteps.value = uniqBy(productTemplatesData.value.product_templates
        .filter((product: Product) => selectedProducts.value.includes(product.id))
        .map((product: Product) => {
            const steps = []

            for (const step of product?.steps ?? []) {
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

    const nodeResponse = await nodeStore.updateNode(formNode.value.id, {
        name: formNode.value.name,
        description: formNode.value.description,
        typeOfProcess: formNode.value.process?.value ? `/api/processes/${formNode.value.process.value}` : null,
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
        nodeTemplate: formNode.value.template ? `/api/product_templates/${formNode.value.template}` : null,
        supplyChainTemplate: supplyChain.value?.id ? `/api/supply_chain_templates/${supplyChain.value.id}` : null,
    })

    if (!nodeResponse) {
        return
    }

    isEditNodeModalOpen.value = false
}

// Remove NULL when something else is selected and add null when nothing is selected
watch(() => selectedPreviousNode.value, (newVal: Array<string | null>, oldVal: Array<string | null>) => {
    if (newVal.length === 0) {
        selectedPreviousNode.value = [null]
        formNode.value.previousNode = [null]
    }

    if (!oldVal.includes(null) && newVal.includes(null)) {
        selectedPreviousNode.value = selectedPreviousNode.value.filter((id: string) => id === null)
        formNode.value.previousNode = selectedPreviousNode.value

        return
    }

    if (newVal.length > 1 && newVal.includes(null)) {
        selectedPreviousNode.value = selectedPreviousNode.value.filter((id: string) => id !== null)
        formNode.value.previousNode = selectedPreviousNode.value
    }

    if (newVal.length === 1 && newVal[0] === null) {
        return
    }

    if (newVal === oldVal || (newVal.length === 1 && newVal[0] === null)) {
        return
    }

    const nonNullValues = newVal?.filter(Boolean)

    if (nonNullValues?.length === 0) {
        formNode.value.previousNode = [null]
    } else {
        formNode.value.previousNode = nonNullValues
    }
})

watch(() => selectedNextNode.value, (newVal: Array<string | null>, oldVal: Array<string | null>) => {
    if (newVal.length === 0) {
        selectedNextNode.value = [null]
        formNode.value.nextNode = [null]
    }

    if (!oldVal.includes(null) && newVal.includes(null)) {
        selectedNextNode.value = selectedNextNode.value.filter((id: string) => id === null)
        formNode.value.nextNode = selectedNextNode.value

        return
    }

    if (newVal.length > 1 && newVal.includes(null)) {
        selectedNextNode.value = selectedNextNode.value.filter((id: string) => id !== null)
        formNode.value.nextNode = selectedNextNode.value
    }

    if (newVal.length === 1 && newVal[0] === null) {
        return
    }

    if (newVal === oldVal || (newVal.length === 1 && newVal[0] === null)) {
        return
    }

    const nonNullValues = newVal?.filter(Boolean)

    if (nonNullValues?.length === 0) {
        formNode.value.nextNode = [null]
    } else {
        formNode.value.nextNode = nonNullValues
    }
})
</script>

<template>
    <ModalLayout
        :is-open="isEditNodeModalOpen"
        name="edit-node-modal"
        :title="$t('supplyChains.editNode')"
        button-submit-text="Save"
        class="edit-node"
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
                        item-title="title"
                        item-value="value"
                        :label="$t('products.selectProcess')"
                        :items="uniqBy(processItems, 'value')"
                        :rules="processRules"
                        variant="outlined"
                        return-object
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
                        :items="uniqBy(productTemplates, 'value')"
                        multiple
                        :label="$t('supplyChains.node.product')"
                        variant="outlined"
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
                        v-model="formNode.template"
                        :items="nodeTemplates"
                        :label="$t('supplyChains.node.template')"
                        variant="outlined"
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
                {{ $t('supplyChains.editNode') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.edit-node.modal-mask {
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
