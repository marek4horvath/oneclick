<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { axiosIns } from '@/plugins/axios'

interface Connection {
    from: string
    to: string
}

interface Message {
    text: string
    type: string
}

interface Item {
    value: string
    title: string
}

const { t } = useI18n()
const route = useRoute()
const isLoading = ref<boolean>(true)
const nodes = ref<any[]>([])
const url = route.path
const parts = url.split('/')
const supplyId = ref(parts[2])
const connectionNode = ref<Connection[]>([])
const processData = ref([])
const tables = ref<Array<{ id: string; name: string; inputData: any[] }>>([])
const messages = ref<Message[]>([])
const inputVisibleInNode = ref<Item[]>([])

const tableHeaderInput = ref([
    { text: t('inputName'), key: 'name', sortable: true, sortAsc: true },
    { text: t('nextNodeNames'), key: 'nextNode', sortable: true, sortAsc: true },
    { text: t('nodesVisiblesIn'), key: 'nodesVisibles', sortable: true, sortAsc: true, customContent: true },
])

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
                        to: node.id,
                        from: targetNode.id,
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

const fetchProcessNode = async () => {
    const getProcessesResponse = await axiosIns.get('processes?processType=node')

    if (!getProcessesResponse) {
        return
    }

    const process = getProcessesResponse.data['hydra:member']

    processData.value = process
}

const fetchNodeData = async () => {
    isLoading.value = true

    const getNodeResponse = await axiosIns.get(`supply_chain_templates/${supplyId.value}`)

    if (!getNodeResponse) {
        return
    }

    await Promise.all(getNodeResponse.data.nodes.map(async (node: any) => {
        node.colorBgNode = node.typeOfProcess?.color
    }))

    nodes.value = getNodeResponse.data.nodes
    connectionNode.value = selectConnectionNode.value

    isLoading.value = false
}

const getAllParentsData = (parents: any[]): { id: string; name: string }[] => {
    const result: { id: string; name: string }[] = []
    const seenIds = new Set<string>()

    const traverseParents = (nodesData: any[]) => {
        for (const node of nodesData) {
            if (node.name && node.id && !seenIds.has(node.id)) {
                seenIds.add(node.id)
                result.push({ id: node.id, name: node.name })
            }
            if (node.parents && node.parents.length > 0) {
                traverseParents(node.parents)
            }
        }
    }

    traverseParents(parents)

    return result
}

const selectionNodes = async (selectNodes: any[], removedNodeIds: string[] | undefined) => {
    const validRemovedNodeIds = removedNodeIds || []

    const updatedTables = selectNodes?.selectedNodes.map(async node => {
        const inputDataArray: any[] = []

        if (validRemovedNodeIds.includes(node.id)) {
            return null
        }

        const parents = node.parents
        const parentsData = getAllParentsData(parents)

        inputVisibleInNode.value = parentsData.map(parent => {
            return {
                value: parent.id,
                title: parent.name,
            }
        })

        const nextNode = parentsData.map(parent => parent.name).join(', ')
        const nodeId = node.id
        const getNodesVisibles = localStorage.getItem('nodesVisibles')
        let nodesVisiblesData: any[] = []

        if (getNodesVisibles) {
            const parsedData = JSON.parse(getNodesVisibles)

            nodesVisiblesData = parsedData.filter((nodeVisible: any) => nodeVisible.idSource === nodeId)
        }

        for (const step of node.steps) {
            for (const input of step.inputs) {
                if (!input) {
                    continue
                }

                input.nextNode = nextNode
                input.nodeId = nodeId
                input.parents = parents
                input.node = node
                input.assignInputsToNode = nodesVisiblesData.filter(nodeVisibles => nodeVisibles.idInput === input.id)[0]?.idTargets || null
                inputDataArray.push(input)
            }
        }
        isLoading.value = false

        return { id: node.id, name: node.name, inputData: inputDataArray }
    })

    tables.value = (await Promise.all(updatedTables)).filter((node: any) => node !== null)
}

const handleSelectionChange = (row: any, assignInputsToNode: any[]) => {
    const existingData = localStorage.getItem('nodesVisibles')
    let dataInStorage: any[] = existingData ? JSON.parse(existingData) : []

    dataInStorage = dataInStorage.filter(item => !(item.idSource === row.nodeId && item.idInput === row.id))

    if (assignInputsToNode.length > 0) {
        const newData = {
            idSource: row.nodeId,
            name: row?.node.name || '',
            idTargets: assignInputsToNode,
            idInput: row.id,
            inputName: row.name,
        }

        dataInStorage.push(newData)
    }

    localStorage.setItem('nodesVisibles', JSON.stringify(dataInStorage))
}

onMounted(async () => {
    await fetchProcessNode()
    await fetchNodeData()
})
</script>

<template>
    <div>
        <DashBoard :messages="messages">
            <template #content>
                <div class="companies">
                    <div class="page-header">
                        <h1>{{ t("dataSharingPolicy.header") }}</h1>
                    </div>
                </div>

                <TreeFlow
                    key="flow"
                    class="tree-dpp"
                    :data="nodes"
                    :connection-node="connectionNode"
                    is-hover-node
                    :is-show-menu="false"
                    :hidden-checkbox="false"
                    hidden-count
                    @toggle-node-selection="selectionNodes"
                />

                <div class="tables">
                    <div
                        v-for="table in tables"
                        :key="table.id"
                        class="table"
                    >
                        <TableComponent
                            :data="table.inputData"
                            :is-mass-action="false"
                            :headers="tableHeaderInput"
                            :header="`${t('nodeName')} ${table.name}`"
                            is-popup
                            id-popup="edit-popup"
                            class="supply-tab bg-white process"
                        >
                            <template #custom-content="{ item }">
                                <VSelect
                                    v-model="item.assignInputsToNode"
                                    :items="inputVisibleInNode"
                                    multiple
                                    :placeholder="t('dataSharingPolicy.btn')"
                                    class="select-node-visible"
                                    @update:model-value="(assignInputsToNode: any) => handleSelectionChange(item, assignInputsToNode)"
                                />
                            </template>
                        </TableComponent>
                    </div>
                </div>
                <LoadingComponent v-if="isLoading" />
            </template>
        </DashBoard>
    </div>
</template>

<style lang="scss">
.tables {
    .table {
        margin: 20px 0px;
        .filterable-table {
            padding-bottom: 66px !important;

            .data-sharing {
                margin-top: 20px;
                margin-bottom: 20px;
                background-color: #65C09E !important;
            }

            table {
                tbody {
                    tr {
                        td.next-node-names {
                            div {
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                                max-width: 330px;
                            }
                        }
                    }
                }
            }
        }
    }
}

.select-node-visible {
    color: #9d9d9d;
}
</style>
