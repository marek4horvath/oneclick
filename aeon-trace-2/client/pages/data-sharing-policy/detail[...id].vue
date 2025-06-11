<script setup lang="ts">
import { uniqBy } from "lodash"
import TreeFlow from '~/components/tree/TreeFlow.vue'
import type { SupplyChain } from "~/types/api/supplyChains.ts"
import type { Nodes } from "~/types/api/nodes.ts"
import type { TableData } from "~/types/tableData.ts"

definePageMeta({
    title: 'page.dataSharingPolicy.title',
    name: 'detail-data-sharing-policy',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

interface Connection {
    from: string
    to: string
}

interface Item {
    value: string
    title: string
}

const { isCompanyManager } = useRoleAccess()
const { t } = useI18n()
const { $listen } = useNuxtApp()
const supplyChainStore = useSupplyChainStore()
const nodesStore = useNodesStore()
const processStore = useProcessStore()
const inputStore = useInputsStore()

const route = useRoute()
const url = route.path
const supplyChainId = url.split('/').pop()
const supplyChain = ref<SupplyChain>()

const isLoading = ref<boolean>(true)
const isLoadingTable = ref<boolean>(true)
const refreshKey = ref(0)

const tableHeaderInput = ref([
    { key: '', title: '' },
    { key: 'name', title: t('dataSharing.tableHeader.name') },
    { key: 'nextNode', title: t('dataSharing.tableHeader.nextNode') },
    { key: 'actions', title: t('dataSharing.tableHeader.dataSharedWith') },
])

const nodes = ref<Nodes[]>([])
const tables = ref<TableData[]>([])
const inputVisibleInNode = ref<Item[]>([])
const connectionNode = ref<Connection[]>([])
const processData = ref([])

const selectedNodesFromTree = ref<Nodes[]>([])
const removedNodes = ref<string[]>([])

const selectConnectionNode = computed(() => {
    const connectionsSet = new Set()

    return [
        ...nodes.value.flatMap(node => {
            if (!node.children || node.children.length === 0) {
                return []
            }

            return node.children.map((child: any) => {
                const targetNode = nodes.value.find(n => n.id === child.id)
                if (targetNode && node.id !== targetNode.id) {
                    const connection = {
                        from: node.id,
                        to: targetNode.id,
                    }

                    const forwardKey = `${connection.from}-${connection.to}`
                    const reverseKey = `${connection.to}-${connection.from}`

                    if (!connectionsSet.has(forwardKey) && !connectionsSet.has(reverseKey)) {
                        connectionsSet.add(forwardKey)

                        return connection
                    }
                }

                return null
            }).filter((connection: any) => connection !== null)
        }),
    ]
})

const fetchSupplyChain = async () => {
    if (supplyChainId === undefined) {
        return
    }

    await supplyChainStore.fetchSupplyChainTemplateDetail(supplyChainId)
    supplyChain.value = supplyChainStore.getSupplyChainTemplate

    await Promise.all(supplyChain.value.nodes.map(async (node: any) => {
        node.colorBgNode = node.typeOfProcess?.color
        node.process = node.typeOfProcess?.name
    }))

    nodes.value = supplyChain.value.nodes
    connectionNode.value = selectConnectionNode.value

    isLoading.value = false
}

const fetchProcessNode = async () => {
    await processStore.fetchProcesses(undefined, undefined, 'node')

    processData.value = processStore.getProcesses
}

const getInput = async (id?: string) => {
    if (id === undefined) {
        return
    }

    const input = await inputStore.fetchInputById(id)

    if (!input) {
        return
    }

    return input
}

const getAllChildrenData = (children: any[]): { id: string; name: string }[] => {
    const result: { id: string; name: string }[] = []
    const seenIds = new Set<string>()

    const traverseChildren = (nodesData: any[]) => {
        for (const node of nodesData) {
            if (typeof node === 'string') {
                continue
            }

            if (node.id && node.name && !seenIds.has(node.id)) {
                seenIds.add(node.id)
                result.push({ id: node.id, name: node.name })
            }

            if (Array.isArray(node.children) && node.children.length > 0) {
                traverseChildren(node.children)
            }
        }
    }

    traverseChildren(children)

    return result
}

const selectNodes = async (selectedNodes: Nodes[], removedNodeIds: string[] | undefined) => {
    const validRemovedNodeIds = removedNodeIds || []

    const updatedTables = selectedNodes?.map(async node => {
        const inputDataArray: any[] = []

        if (validRemovedNodeIds.includes(node.id)) {
            return null
        }

        const getChildren = await nodesStore.fetchChildNodesById(node.id)
        const childrenData = getAllChildrenData(getChildren.children)

        inputVisibleInNode.value[node.id] = childrenData.map(child => {
            return {
                value: child.id,
                title: child.name,
            }
        })

        const nextNode = childrenData.map(child => child.name).join(', ')
        const nodeId = node.id
        const getNodesVisible = localStorage.getItem('nodesVisible')
        let nodesVisibleData: any[] = []

        if (getNodesVisible) {
            const parsedData = JSON.parse(getNodesVisible)

            nodesVisibleData = parsedData.filter((nodeVisible: any) => nodeVisible.idSource === nodeId)
        }

        for (const step of node?.steps) {
            for (const input of step?.inputs) {
                if (!input) {
                    continue
                }

                try {
                    const inputData = await getInput(input.id)

                    isLoadingTable.value = true
                    inputData.nextNode = nextNode
                    inputData.nodeId = nodeId
                    inputData.children = getChildren.children
                    inputData.node = node
                    inputData.assignInputsToNode = nodesVisibleData.filter(nodeVisible => nodeVisible.idInput === inputData.id)[0]?.idTargets || null

                    inputDataArray.push(inputData)
                } catch (error) {
                    console.error(`Failed to fetch input data: ${error}`)
                }
            }
        }

        isLoadingTable.value = false

        return {
            headers: tableHeaderInput,
            name: node.name,
            data: inputDataArray,
            idTable: `table-${node.name}`,
            totalItems: inputDataArray?.length,
        }
    })

    if (updatedTables === undefined) {
        return
    }

    tables.value = (await Promise.all(updatedTables)).filter((node: any) => node !== null)
}

const handleSelectionChange = (row: any, assignInputsToNode: any[]) => {
    const existingData = localStorage.getItem('nodesVisible')
    let dataInStorage: any[] = existingData ? JSON.parse(existingData) : []

    dataInStorage = dataInStorage.filter(item => !(item.idSource === row.nodeId && item.idInput === row.id))

    if (assignInputsToNode.length > 0) {
        const newData = {
            idSource: row.nodeId,
            name: row?.node.name || '',
            idTargets: assignInputsToNode.map(i => typeof i === 'object' ? i.value : i),
            idInput: row.id,
            inputName: row.name,
        }

        dataInStorage.push(newData)
    }

    try {
        localStorage.setItem('nodesVisible', JSON.stringify(dataInStorage))
    } catch (e: any) {
        if (e.name === 'QuotaExceededError' || e.code === 22) {
            console.error('LocalStorage is full!', e)
        } else {
            console.error('Unknown error while saving to localStorage:', e)
        }
    }
}

$listen('openNodeTable', async ({ node, checked }) => {
    const indexOfSelected = selectedNodesFromTree.value.findIndex(n => n.id === node.id)
    const indexOfRemoved = removedNodes.value.findIndex(n => n === node.id)

    if (!checked) {
        selectedNodesFromTree.value.splice(indexOfSelected, 1)
        removedNodes.value.push(node.id)
    }

    if (indexOfSelected === -1 || checked) {
        selectedNodesFromTree.value.push(node)
        removedNodes.value.splice(indexOfRemoved, 1)
    }

    await selectNodes(selectedNodesFromTree.value, removedNodes.value)
})

onMounted(async () => {
    await fetchProcessNode()
    await fetchSupplyChain()
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer
            fluid
            class="supply-chain-detail"
        >
            <header>
                <VRow v-if="!isLoading">
                    <VCol
                        cols="12"
                        md="9"
                    >
                        <VRow class="supply-chain-name mt-1 ms-0">
                            <h1>{{ `${supplyChain?.name}` }}</h1>
                        </VRow>
                    </VCol>
                </VRow>
            </header>
            <VCard class="tree-container">
                <TreeFlow
                    :key="refreshKey"
                    :data="nodes"
                    connection-key="parents"
                    traversal="forward"
                    is-populated-by-supply-chain-template
                    disable-counts
                    disable-options
                />
            </VCard>
            <div v-if="!isLoadingTable">
                <div
                    v-for="table in tables"
                    :key="table.id"
                    class="table"
                >
                    <TableData
                        key="supplyChainTable"
                        search="search"
                        :items="table"
                        :is-delete="false"
                        :loading="isLoading"
                    >
                        <template #table-title>
                            <div class="mx-5 w-100 d-flex align-center justify-space-between">
                                <div class="header-title">
                                    <h3>
                                        {{ table.name }}
                                    </h3>
                                </div>
                            </div>
                        </template>

                        <template #table-actions="{ item }">
                            <div
                                v-if="item"
                                class="actions"
                            >
                                <VSelect
                                    v-model="item.assignInputsToNode"
                                    :items="uniqBy(inputVisibleInNode[item.nodeId], 'value')"
                                    multiple
                                    variant="outlined"
                                    :placeholder="t('dataSharing.btn')"
                                    class="select-node-visible mt-6"
                                    :disabled="!isCompanyManager()"
                                    @update:model-value="(assignInputsToNode: any) => handleSelectionChange(item, assignInputsToNode)"
                                />
                            </div>
                        </template>
                    </TableData>
                </div>
            </div>
        </VContainer>
    </NuxtLayout>
</template>

<style lang="scss">
.tree-container {
    margin: 2rem auto;
}
</style>
