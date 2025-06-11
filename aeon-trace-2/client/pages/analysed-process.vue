<script setup lang="ts">
import Draggable from 'vuedraggable'
import ContextMenu from '@imengyu/vue3-context-menu'
import Sort from '@/components/Sort.vue'

interface ProductInfo {
    name: string | null
    company: string | null
    numberStep: number | null
    numberInput: number | null
    numberSubProduct: number | null
    img: string | undefined
}

interface Item {
    value: string
    title: string
}

interface SimpleData {
    title: string
    value: string
}

definePageMeta({
    title: 'page.dashboard.title',
    name: 'analysed-process',
    layout: 'dashboard',
    middleware: 'auth',
    breadcrumbs: ['dashboard'],
})

const { $listen } = useNuxtApp()
const { t } = useI18n()
const configData = ref({})

const data = ref<SimpleData[]>([])

const productId = ref('')
const processData = ref()

const isConfigOpen = ref(false)
const isMenuOpen = ref(false)
const isLoading = ref(true)

const stepsNode = ref()
const selectedNodes = ref([])

const products = ref([])
const processItems = ref<Item[]>([])
const selectInputs = ref([])

const productInfo = ref<ProductInfo>({
    name: null,
    company: null,
    numberStep: null,
    numberInput: null,
    numberSubProduct: null,
    img: '',
})

const steps = ref<Array<any>>([])

const productsStore = useProductsStore()
const stepsTemplateStore = useStepsTemplateStore()
const stepsStore = useStepsStore()
const processStore = useProcessStore()

const widgets = ref([
    { id: 1, name: t('analysedProcess.widgetName.productionCost'), widgetTitle: t('analysedProcess.widgetTitle.decisionTimeSave'), widgetValue: '9:82 min' },
    { id: 2, name: t('analysedProcess.widgetName.workingHoursPerProducts'), widgetTitle: t('analysedProcess.widgetTitle.profitFromAnalyze'), widgetValue: '2.300k' },
    { id: 3, name: t('analysedProcess.widgetName.airPollution'), widgetTitle: t('analysedProcess.widgetTitle.co2Emissions'), widgetValue: '04' },
    { id: 4, name: t('analysedProcess.widgetName.cycleTimePerProduct'), widgetTitle: t('analysedProcess.widgetTitle.producedProducts'), widgetValue: '32' },
    { id: 5, name: t('analysedProcess.widgetName.illustrations'), widgetTitle: t('analysedProcess.widgetTitle.qualityInspection'), widgetValue: '28' },
    { id: 6, name: t('analysedProcess.widgetName.productionCost'), widgetTitle: t('analysedProcess.widgetTitle.decisionTimeSave'), widgetValue: '9:82 min' },
    { id: 7, name: t('analysedProcess.widgetName.workingHoursPerProducts'), widgetTitle: t('analysedProcess.widgetTitle.profitFromAnalyze'), widgetValue: '2.300k' },
    { id: 8, name: t('analysedProcess.widgetName.airPollution'), widgetTitle: t('analysedProcess.widgetTitle.co2Emissions'), widgetValue: '04' },
    { id: 9, name: t('analysedProcess.widgetName.cycleTimePerProduct'), widgetTitle: t('analysedProcess.widgetTitle.producedProducts'), widgetValue: '32' },
    { id: 10, name: t('analysedProcess.widgetName.illustrations'), widgetTitle: t('analysedProcess.widgetTitle.qualityInspection'), widgetValue: '28' },
])

const fetchProducts = async () => {
    try {
        await productsStore.fetchProducts()

        const response = productsStore.products

        products.value = response
    } catch (error) {
        console.error('Error fetching products:', error)
    } finally {
        isLoading.value = false
    }
}

const fetchStepData = async () => {
    const getProductResponse = await productsStore.fetchProductById(productId.value)

    if (!getProductResponse) {
        return
    }

    let companyName = ''

    if (getProductResponse.companies?.length !== 0) {
        companyName = getProductResponse.companies
            .map((company: any) => company.name).join(', ')
    }

    const totalInputs = getProductResponse.stepsTemplate?.steps.reduce((acc: number, step: any) => {
        return acc + (step.inputs ? step.inputs.length : 0)
    }, 0)

    productInfo.value = {
        name: getProductResponse.name,
        company: companyName,
        numberStep: getProductResponse.stepsTemplate?.steps?.length,
        numberInput: totalInputs,
        numberSubProduct: 0,
        img: getProductResponse.productImage,
    }

    const stepsTemplatsId = getProductResponse.stepsTemplate?.id

    if (!stepsTemplatsId) {
        return
    }

    const stepsTemplatsResponse = await stepsTemplateStore.fetchStepTemplateById(stepsTemplatsId)

    if (!stepsTemplatsResponse) {
        return
    }

    const stepsData = stepsTemplatsResponse?.steps.map(async (stepItem: any) => {
        const stepsResponse = await stepsStore.fetchStepById(stepItem.id)

        if (!stepsResponse) {
            return
        }

        return stepsResponse
    })

    steps.value = (await Promise.all(stepsData)).filter(step => step !== null)

    steps.value = [...steps.value]
}

const fetchProcess = async () => {
    await processStore.fetchProcesses(undefined, undefined, 'step')

    const getProcessesResponse = processStore.getProcesses

    if (!getProcessesResponse) {
        return
    }

    processData.value = getProcessesResponse

    processData.value.forEach(processItem => {
        processItems.value.push({
            value: processItem.id,
            title: processItem.name,
        })
    })

    processItems.value = processItems.value.filter(
        (item, index, self) =>
            index === self.findIndex(currentItem => currentItem.value === item.value),
    )
}

const treeSteps = computed(() => {
    if (!steps.value) {
        return []
    }
    const parents: any[] = []

    return steps.value?.map((step: any) => {
        const process = processData.value?.find((processItem: any) => processItem.id === step.process.split('/').pop())

        parents.push({
            id: step.id,
            name: step.name,
        })

        stepsNode.value = parents

        const parentStepsData = step.parentSteps.map((parentItem: any) => ({ ...parentItem }))
        const chidrenStepsData = step.steps.map((childItem: any) => ({ ...childItem }))

        return {
            nodeType: 'step',
            id: step.id,
            name: step.name,
            colorBgNode: process?.color,
            process: process?.name,
            batchTypeOfStep: step.batchTypeOfStep,
            quantity: step.quantity,
            sort: step.sort,
            parents: parentStepsData || [],
            children: chidrenStepsData || [],
            nodePosition: step.stepPosition,
        }
    })
})

const selectedProductName = computed(() => {
    const selectedProduct = products.value.find(p => p.id === productId.value)

    return selectedProduct ? selectedProduct.name : ''
})

const handleCloseConfiguration = () => {
    isConfigOpen.value = false
    configData.value = {}
}

const handleAddConfiguration = () => {
    handleCloseConfiguration()
}

const isChecked = (id: string) => {
    const node = selectInputs.value.find((selectInput: any) => selectInput[configData.id])

    return node ? node[configData.id].includes(id) : false
}

const handleCheckboxChange = (id: string) => {
    const node = selectInputs.value.find((selectInput: any) => selectInput[configData.value.id])

    if (node) {
        const index = node[configData.value.id].indexOf(id)
        if (index === -1) {
            node[configData.value.id].push(id)
        } else {
            node[configData.value.id].splice(index, 1)
        }
    } else {
        selectInputs.value.push({
            [configData.value.id]: [id],
        })
    }
}

$listen('openNodeTable', async ({ node, checked }) => {
    if (checked) {
        if (!selectedNodes.value.find(n => n.id === node.id)) {
            selectedNodes.value.push(node)
        }
    } else {
        selectedNodes.value = selectedNodes.value.filter(n => n.id !== node.id)
    }
})

$listen('openMenu', ({ event, nodeData }) => {
    const sourceNode = nodeData

    widgets.value = widgets.value.map((widget: any) => ({
        ...widget,
        nodeId: sourceNode.id,
    }))

    const menuItems = {
        items: [
            {
                label: t('contextMenu.seeDetail'),
                onClick: () => {
                    configData.value = sourceNode
                    isConfigOpen.value = true
                },
            },
        ],
    }

    ContextMenu.showContextMenu({
        ...menuItems,
        x: event.clientX,
        y: event.clientY,
    })
})

const handleProductSelection = async (id: string) => {
    productId.value = id
    await fetchStepData()
    await fetchProcess()
}

onMounted(async () => {
    await fetchProducts()
})
</script>

<template>
    <NuxtLayout>
        <VContainer fluid>
            <div class="analysed-process">
                <Sort
                    v-model:data="data"
                    request-url="/step-data"
                />
                <VMenu v-model="isMenuOpen">
                    <template #activator="{ props }">
                        <VBtn
                            v-bind="props"
                            class="product-menu-button"
                            :class="isMenuOpen ? 'menu-open' : ''"
                        >
                            <template #default>
                                <span class="product-menu-button-title">
                                    {{ selectedProductName || $t('products.selectProduct') }}
                                </span>
                                <VIcon
                                    size="24"
                                    :icon="isMenuOpen ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"
                                />
                            </template>
                        </VBtn>
                    </template>

                    <VList class="product-menu-list">
                        <VListItem v-if="isLoading">
                            <VListItemTitle>Loading...</VListItemTitle>
                        </VListItem>
                        <VListItem
                            v-for="product in products"
                            v-else
                            :key="product.id"
                            @click="handleProductSelection(product.id)"
                        >
                            <VListItemTitle>{{ product.name }}</VListItemTitle>
                            <VIcon
                                v-if="productId === product.id"
                                icon="ri-check-line"
                                color="#26A69A"
                            />
                        </VListItem>
                    </VList>
                </VMenu>
                <VCard
                    class="analysed-process-tree-container"
                    variant="flat"
                >
                    <TreeFlow
                        key="flow"
                        class="tree-dpp"
                        :data="treeSteps"
                        :is-show-menu="false"
                        is-populated-by-supply-chain-template
                    />

                    <Transition name="fade">
                        <div
                            v-if="!treeSteps || treeSteps.length === 0"
                            class="tree-overlay"
                        >
                            <h3>{{ $t('products.chooseProduct') }}</h3>
                        </div>
                    </Transition>
                </VCard>
                <VDialog
                    v-model="isConfigOpen"
                    persistent
                >
                    <template #default>
                        <VCard class="configuration-dialog">
                            <VCardTitle class="configuration-title">
                                {{ configData.name }}
                                <button
                                    class="title-button"
                                    @click="handleCloseConfiguration"
                                >
                                    <span>X</span>
                                </button>
                            </VCardTitle>
                            <VCard
                                class="sort-widgets"
                                variant="flat"
                            >
                                <Draggable
                                    v-model="widgets"
                                    class="sort-widgets-drag"
                                    group="reports"
                                    item-key="name"
                                >
                                    <template #item="{ element }">
                                        <VCard
                                            variant="flat"
                                            class="widget-card"
                                        >
                                            <VRow>
                                                <VCheckbox
                                                    :value="element.id"
                                                    :checked="isChecked(configData.id)"
                                                    hide-details
                                                    color="#24a69a"
                                                    @change="handleCheckboxChange(element.id)"
                                                />

                                                <VCol class="widget-content-container">
                                                    <h4 class="widget-card-title">
                                                        {{ element.widgetTitle }}
                                                    </h4>
                                                    <p class="widget-card-content">
                                                        {{ element.widgetValue }}
                                                    </p>
                                                </VCol>
                                            </VRow>
                                        </VCard>
                                    </template>
                                </Draggable>
                            </VCard>
                            <div class="submit-button-container">
                                <VBtn
                                    class="submit-button"
                                    text="ADD INPUTS"
                                    @click="handleAddConfiguration"
                                />
                            </div>
                        </VCard>
                    </template>
                </VDialog>
                <VCard
                    v-for="selectedNode in selectedNodes"
                    :key="selectedNode.id"
                    variant="flat"
                    class="selected-node-container"
                >
                    <h3>{{ selectedNode.name }}</h3>
                    <VCard
                        class="sort-widgets"
                        variant="flat"
                    >
                        <Draggable
                            v-model="widgets"
                            class="sort-widgets-drag"
                            group="reports"
                            item-key="name"
                        >
                            <template #item="{ element }">
                                <VCard
                                    variant="flat"
                                    class="widget-card"
                                >
                                    <h4 class="widget-card-title">
                                        {{ element.widgetTitle }}
                                    </h4>
                                    <p class="widget-card-content">
                                        {{ element.widgetValue }}
                                    </p>
                                </VCard>
                            </template>
                        </Draggable>
                    </VCard>
                </VCard>
            </div>
        </VContainer>
    </NuxtLayout>
</template>
