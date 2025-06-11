<script setup lang="ts">
import ContextMenu from '@imengyu/vue3-context-menu'
import AddNode from '@/dialogs/nodes/add-node.vue'
import EditNode from '@/dialogs/nodes/edit-node.vue'
import DetailProcess from '~/dialogs/process/detail-process.vue'
import NodeTemplates from '~/dialogs/supply-chain/node-templates.vue'
import VerifySupplyChain from '~/dialogs/supply-chain/verify-supply-chain.vue'
import DetailJson from '~/dialogs/nodes/detail-json.vue'

definePageMeta({
    name: 'supply-chains-detail',
    layout: 'dashboard',
    middleware: 'auth',
})

const { isCompanyManager } = useRoleAccess()
const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const route = useRoute()

const isLoading = ref<boolean>(true)
const processStore = useProcessStore()
const supplyChainStore = useSupplyChainStore()
const nodesStore = useNodesStore()
const supplyChain = ref<any>(null)
const supplyChainTemplateNode = ref<any>(null)
const supplyChainId = ref<string | null>(null)
const processData = ref([])
const nodes = ref([])

const processNode = ref()

const fetchProcessNode = async () => {
    await processStore.fetchProcesses(undefined, undefined, 'node')

    processData.value = processStore.getProcesses
    processNode.value = processStore.getProcesses
}

const fetchSupplyChain = async () => {
    supplyChainId.value = route.params.id[1]

    const response = await supplyChainStore.fetchSupplyChainTemplateDetail(supplyChainId.value)

    if (response) {
        supplyChain.value = supplyChainStore.getSupplyChainTemplate
    }

    if (!supplyChain.value?.nodes) {
        return
    }

    await Promise.all(supplyChain.value?.nodes?.map(async (node: any) => {
        node.processColor = node.typeOfProcess?.color
        node.process = node.typeOfProcess?.name
        node.nextNode = node.children?.map((child: any) => child.name).join(', ')
        node.previousNode = node.parents?.map((parent: any) => parent.name).join(', ')
        node.subItems = node.steps.map((step: any) => {
            step.inputs = step?.inputs.map((input: any) => {
                return {
                    ...input,
                    processColor: node.typeOfProcess?.color,
                }
            })

            step.processColor = node.typeOfProcess?.color
            step.process = node.typeOfProcess?.name
            step.subItems = step?.inputs || []

            return step
        })
    }))
}

const fetchSupplyChainNodeTemplate = async () => {
    supplyChainId.value = route.params.id[1]

    await supplyChainStore.fetchSupplyChainTemplate(supplyChainId.value)
    supplyChainTemplateNode.value = supplyChainStore.getSupplyChainTemplate
}

onMounted(async () => {
    await fetchProcessNode()
    await fetchSupplyChain()

    processNode.value = processStore.getProcessesByType('node')

    if (supplyChain.value) {
        route.meta.title = supplyChain.value.name
    }

    isLoading.value = false
})

watch(() => supplyChain.value, newValue => {
    if (newValue) {
        nodes.value = newValue.nodes
    }
})

watch(() => nodesStore.nodes, async (newValue: Node[]) => {
    if (newValue) {
        await fetchSupplyChain()
    }
})

const headers = ref([
    { title: t('page.supplyChains.detail.table.headers.name'), key: 'name', class: 'font-weight-bold' },
    { title: t('page.supplyChains.detail.table.headers.next_node'), key: 'nextNode' },
    { title: t('page.supplyChains.detail.table.headers.previous_node'), key: 'previousNode' },
    { title: t('page.supplyChains.detail.table.headers.actions'), key: 'actions', width: '200px', align: 'start' },
])

const subheaders = ref([
    { title: t('page.supplyChains.detail.table.headers.product_step_name'), key: 'name' },
    { title: t('page.supplyChains.detail.table.headers.quantity'), key: 'quantity' },
    { title: t('page.supplyChains.detail.table.headers.number_input'), key: 'inputs' },
])

const inputHeaders = ref([
    { title: t('page.supplyChains.detail.table.headers.input_name'), key: 'name' },
    { title: t('page.supplyChains.detail.table.headers.input_type'), key: 'type' },
    { title: t('page.supplyChains.detail.table.headers.category_name'), key: 'inputCategories' },
])

const openAddNodeModal = async () => {
    $event('openAddNodeModal', supplyChain.value)
}

const openEditNodeModal = (node: any) => {
    $event('openEditNodeModal', { node, supplyChain: supplyChain.value })
}

const openAddProcessModal = async () => {
    $event('openDetailProcessModal', 'node')
}

const openNodeTemplatesModal = async () => {
    await fetchSupplyChainNodeTemplate()

    $event('openNodeTemplatesModal', { supplyChain: supplyChainTemplateNode.value })
}

const openVerifySupplyChain = () => {
    $event('openVerifySupplyChainModal', supplyChainId.value)
}

const removeNode = async (node: any) => {
    await nodesStore.deleteNode(node.id)

    nodes.value = nodes.value.filter((n: any) => n.id !== node.id)
        .map((n: any) => {
            n.siblings = n.siblings?.filter((s: any) => s.id !== node.id)

            return n
        })
}

$listen('handleNodeSubmitted', async () => {
    await fetchSupplyChain()
})

$listen('nodesConnected', async (params: any) => {
    const sourceNodeId = params.source
    const targetNodeId = params.target

    if (!sourceNodeId || !targetNodeId) {
        return
    }

    const sourceNode = nodes.value.find((n: any) => n.id === sourceNodeId)
    const targetNode = nodes.value.find((n: any) => n.id === targetNodeId)

    if (!sourceNode || !targetNode) {
        return
    }

    await nodesStore.updateNode(sourceNode.id, {
        children: [
            ...(sourceNode.children.length > 0
                ? sourceNode.children
                    .map((child: any) => typeof child !== "string" ? `/api/nodes/${child.id}` : null)
                    .filter(Boolean)
                : []
            ),
            `/api/nodes/${targetNode.id}`,
        ],
    })
    await fetchSupplyChain()
})

$listen('handleProcessEditSubmitted', async () => {
    await fetchProcessNode()
})

$listen('handleProcessDeleteSubmitted', async () => {
    await fetchProcessNode()
})

$listen('handleProcessAddSubmitted', async () => {
    await fetchProcessNode()
})

$listen('openMenu', ({ event, nodeData }) => {
    const sourceNode = nodeData

    const menuItems = {
        items: [
            {
                label: t('contextMenu.showJson', { nodeName: sourceNode.name }),
                onClick: () => {
                    $event('openDetailJsonModal', sourceNode)
                },
            },
            ...(isCompanyManager()
                ? [
                    {
                        label: t('contextMenu.editNode'),
                        onClick: () => {
                            $event('openEditNodeModal', { node: sourceNode, supplyChain: supplyChain.value })
                        },
                    },
                ]
                : []),
        ],
    }

    ContextMenu.showContextMenu({
        ...menuItems,
        x: event.clientX,
        y: event.clientY,
    })
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer
            fluid
            class="detail-product"
        >
            <div
                v-if="!isLoading"
                class="accordion pb-4"
            >
                <AccordionTable
                    :headers="headers"
                    :items="nodes"
                    hidden-tag
                    fixed-width
                >
                    <template #accordion-header>
                        <div class="accordion-header">
                            <div class="w-100 d-flex align-center justify-space-between">
                                <div class="header-title" />

                                <div class="d-flex">
                                    <div class="search-wrap me-4" />

                                    <div
                                        v-if="isCompanyManager()"
                                        class="actions"
                                    >
                                        <VBtn
                                            class="me-1 mt-3 mb-5 text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openAddNodeModal"
                                        >
                                            {{ $t('supplyChains.addNode') }}
                                        </VBtn>
                                        <VBtn
                                            class="me-1 mt-3 mb-5 text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openAddProcessModal"
                                        >
                                            {{ $t('products.addProcess') }}
                                        </VBtn>
                                        <VBtn
                                            class="me-1 mt-3 mb-5 text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openNodeTemplatesModal"
                                        >
                                            {{ $t('supplyChains.nodeTemplates') }}
                                        </VBtn>
                                        <VBtn
                                            class="me-1 mt-3 mb-5 text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openVerifySupplyChain"
                                        >
                                            {{ $t('supplyChains.verifySupplyChain') }}
                                        </VBtn>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template #custom-coll="{ item, header }">
                        {{ item[header.key] ?? '----' }}
                    </template>

                    <template #accordion-color-tags="{ item }">
                        <div
                            :style="{
                                height: '100%',
                                border: `1px solid ${item.process.color}`,
                                width: '0.1rem',
                            }"
                        />
                    </template>

                    <template #custom-actions="{ item }">
                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            @click="openEditNodeModal(item)"
                        >
                            <PhosphorIconPencilSimpleLine
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>

                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                        >
                            <PhosphorIconHand
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>

                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            @click="removeNode(item)"
                        >
                            <PhosphorIconTrash
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>
                    </template>

                    <template #accordion-content="{ item }">
                        <AccordionTable
                            :headers="subheaders"
                            :items="item?.steps"
                            fixed-width
                            hidden-footer
                            class="accordion-input"
                        >
                            <template #accordion-color-tags>
                                <div
                                    style="height: 100%;
                                    border: 1px solid;
                                    width: 0.1rem;"
                                />
                            </template>
                            <template #custom-coll="step">
                                <div v-if="step.header.key === 'inputs'">
                                    {{ step.item[step.header.key]?.length || 0 }}
                                </div>
                                <div v-else>
                                    {{ step.item[step.header.key] ?? '----' }}
                                </div>
                            </template>

                            <template #accordion-content="{ item }">
                                <AccordionTable
                                    :headers="inputHeaders"
                                    :items="item?.inputs"
                                    fixed-width
                                    hidden-footer
                                    class="accordion-input"
                                >
                                    <template #accordion-color-tags>
                                        <div
                                            style="height: 100%;
                                            border: 1px solid;
                                            width: 0.1rem;"
                                        />
                                    </template>
                                    <template #custom-coll="{ item, header }">
                                        <div v-if="header.key === 'type'">
                                            <div class="d-flex align-center">
                                                <IconTypeInputs
                                                    class="me-2"
                                                    :type="item[header.key]"
                                                />
                                                {{ item[header.key] ?? '----' }}
                                            </div>
                                        </div>
                                        <div v-else-if="header.key === 'inputCategories'">
                                            <div class="d-flex align-center">
                                                {{ item[header.key].map((category: any) => category.name).join(', ') || '----' }}
                                            </div>
                                        </div>
                                        <div v-else>
                                            {{ item[header.key] ?? '----' }}
                                            {{ item?.options?.length ? `(${item?.options.join(', ')})` : '' }}
                                        </div>
                                    </template>
                                </AccordionTable>
                            </template>
                        </AccordionTable>
                    </template>
                </AccordionTable>
            </div>

            <Legend
                v-if="processNode"
                style="width: 100%"
                class="mt-8"
                :data="processNode"
            />

            <VCard class="tree-container">
                <TreeFlow
                    :data="nodes"
                    connection-key="parents"
                    traversal="forward"
                    disable-counts
                    allow-plus
                />
            </VCard>
        </VContainer>
    </NuxtLayout>

    <AddNode />
    <EditNode />
    <DetailProcess />
    <DetailJson />
    <NodeTemplates />
    <VerifySupplyChain />
</template>

<style lang="scss">
.tree-container {
    margin: 2rem auto;
}
</style>
