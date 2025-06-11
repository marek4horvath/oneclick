<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { MarkerType, VueFlow, useVueFlow } from '@vue-flow/core'
import { Background } from '@vue-flow/background'
import { ControlButton, Controls } from '@vue-flow/controls'
import ContextMenu from '@imengyu/vue3-context-menu' // eslint-disable-line import/no-named-as-default
import CustomEdge from './CustomEdge.vue'
import CustomNode from './CustomNode.vue'
import { useFlowLayout } from '@/utils/useLayout'
import Popup from '@/components/popup.vue'
import { PhArrowsCounterClockwise } from '@phosphor-icons/vue'

const props = defineProps({
    data: {
        type: [Array, Object],
        required: true,
    },
    isClickNode: {
        type: Boolean,
        default: false,
    },
    isHoverNode: {
        type: Boolean,
        default: false,
    },
    isProduct: {
        type: Boolean,
        default: false,
    },
    isDraggableNode: {
        type: Boolean,
        default: true,
    },
    isShowPopup: {
        type: Boolean,
        default: false,
    },
    idPopup: {
        type: String,
        default: '',
    },
    changeOfStatus: {
        type: Boolean,
    },
    connectionNode: {
        type: Array,
        default: () => [],
    },
    colorLineConnections: {
        type: Array as () => string[],
        default: () => [],
    },
    brokenLine: {
        type: Boolean,
        default: false,
    },

    isNextStep: {
        type: Boolean,
        default: false,
    },

    showCircleBtn: {
        type: Boolean,
        default: false,
    },
    showCirclePopup: {
        type: Boolean,
        default: false,
    },
    isValid: {
        type: Boolean,
        default: false,
    },
    showInfoNode: {
        type: Boolean,
        default: false,
    },
    offClosePopup: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['closeModal', 'nodeData', 'dppData', 'showJson', 'circlePopupData', 'circleInfoPopupData', 'getNodeHover', 'removeNodeHover'])

const circlePopupData = ref({
    parent: [],
    child: [],
    isExport: false,
})

const { t } = useI18n()
const localData = ref(props.data)
const showCirclePopup_ = ref<boolean>(props.showCirclePopup)
const showModal = ref(props.isShowPopup)
const isValid_ = ref<boolean>(props.isValid)
const selectedNode = ref(null)
const { layout } = useFlowLayout()
const { fitView, onEdgeClick, onNodeClick, onNodeDragStop } = useVueFlow()

const showDppData = params => {
    if (params.isNodeJson === 'true') {
        emit('showJson', params.nodeData)
    }

    if (!params.nodeData) {
        return
    }

    params.nodeData.isNodeJson = params.isNodeJson === 'true'
    params.nodeData.isExportDpp = params.isExportDpp === 'true'
    params.nodeData.isConnectLogistics = params.isConnectLogistics === 'true'
    params.nodeData.isOngoingDpp = params.isOngoingDpp === 'true'
    if (params.nodeData) {
        emit('dppData', params.nodeData)
    }
}

const prepareCirclePopupData = (parent: object, child: object, isExport: boolean) => {
    circlePopupData.value = {
        parent: {
            name: parent?.name,
            id: parent?.id,
            dpps: parent?.dppData,
            fromNodeLogistics: parent?.fromNodeLogistics,
            toNodeLogistics: parent?.toNodeLogistics,
            logisticData: parent.logisticData,
            countDpp: parent?.countDpp,
            countLogistics: parent?.countLogistics,
            existAssignedDpp: parent?.existAssignedDpp,
            existLogisticsAssignedToDpp: parent?.existLogisticsAssignedToDpp,
        },
        child: {
            name: child?.name,
            id: child?.id,
            dpps: child?.dppData,
            fromNodeLogistics: child?.fromNodeLogistics,
            toNodeLogistics: child?.toNodeLogistics,
            logisticData: child.logisticData,
            countDpp: child?.countDpp,
            countLogistics: child?.countLogistics,
            existAssignedDpp: child?.existAssignedDpp,
            existLogisticsAssignedToDpp: child?.existLogisticsAssignedToDpp,
        },
        isExport,
    }

    return circlePopupData.value
}

const showMenu = (params: any) => {
    const sourceNode = (params.node) ? params.node : params.edge.sourceNode
    const targetNode = (params.node) ? null : params.edge.targetNode

    const sourceNodeData = localData.value.find((d: any) => d.id === sourceNode.id)
    const targetNodeData = targetNode ? localData.value.find((d: any) => d.id === targetNode.id) : null

    const menuItems = {
        items: [
            {
                label: props.isProduct ? t('contextMenu.showInputs') : t('contextMenu.addDpp'),

                onClick: () => {
                    if (!props.isClickNode) {
                        return
                    }

                    let parent = 0

                    if (sourceNodeData.parents === undefined) {
                        parent = sourceNodeData.parent?.id || 0
                    } else {
                        parent = sourceNodeData.parents?.[0]?.id || 0
                    }

                    selectedNode.value = {
                        parent: !targetNodeData
                            ? {
                                id: 0,
                                name: 'Root',
                                parent: null,
                            }
                            : {
                                id: targetNodeData?.id,
                                name: targetNodeData?.name,
                                companyName: targetNodeData?.companyName,
                                productName: targetNodeData?.productName,
                                companySiteName: targetNodeData?.companySiteName,
                                companiesFromProductTemplates: targetNodeData?.companiesFromProductTemplates,
                                productTemplates: targetNodeData?.productTemplates,
                                parent: sourceNodeData?.id || 0,
                                additionalParents: targetNodeData?.parents?.slice(1)?.map((p: any) => p.id) || [],
                                additionalParentsData: targetNodeData?.parents?.slice(1)?.map((p: any) => ({
                                    ...p,
                                    dppData: localData.value.filter(it => it.id === p.id).map((it: any) => it.dppData || [])[0],
                                    logisticData: localData.value.find(it => it.id === p.id)?.logisticData || [],
                                })) || [],
                                isError: targetNodeData?.isError,
                                childrenNode: targetNodeData?.childrenNode,
                                parentName: sourceNodeData?.name,
                                parentInputs: targetNodeData?.parentInputs,
                                inputs: targetNodeData?.inputs,
                                lockedNode: targetNodeData?.lockedNode,
                                steps: targetNodeData?.steps,
                                connectLogisticsCount: targetNodeData?.connectLogisticsCount,
                                ongoingDppCount: targetNodeData?.ongoingDppCount,
                                dppData: targetNodeData?.dppData,
                                fromNodeLogistics: targetNodeData?.fromNodeLogistics,
                                toNodeLogistics: targetNodeData?.toNodeLogistics,
                                logisticData: targetNodeData?.logisticData,
                                colorBgNode: targetNodeData?.colorBgNode,
                                countDpp: targetNodeData?.countDpp,
                                countLogistics: targetNodeData?.countLogistics,
                                existAssignedDpp: targetNodeData?.existAssignedDpp,
                                existLogisticsAssignedToDpp: targetNodeData?.existLogisticsAssignedToDpp,
                            },
                        children: {
                            id: sourceNodeData?.id,
                            name: sourceNodeData?.name,
                            companyName: sourceNodeData?.companyName,
                            productName: sourceNodeData?.productName,
                            companySiteName: sourceNodeData?.companySiteName,
                            companiesFromProductTemplates: sourceNodeData?.companiesFromProductTemplates,
                            productTemplates: sourceNodeData?.productTemplates,
                            parent,
                            additionalParents: targetNodeData?.parents?.slice(1)?.map((p: any) => p.id) || [],
                            additionalParentsData: targetNodeData?.parents?.slice(1)?.map((p: any) => ({
                                ...p,
                                dppData: localData.value.filter(it => it.id === p.id).map((it: any) => it.dppData || [])[0],
                                logisticData: localData.value.find(it => it.id === p.id)?.logisticData || [],
                            })) || [],
                            isError: sourceNodeData?.isError,
                            childrenNode: sourceNodeData?.childrenNode,
                            parentName: sourceNodeData?.parent?.name || null,
                            parentInputs: sourceNodeData?.parent?.inputs || null,
                            inputs: sourceNodeData?.inputs,
                            lockedNode: sourceNodeData?.lockedNode,
                            steps: sourceNodeData?.steps,
                            connectLogisticsCount: sourceNodeData?.connectLogisticsCount,
                            ongoingDppCount: sourceNodeData?.ongoingDppCount,
                            dppData: sourceNodeData?.dppData,
                            fromNodeLogistics: sourceNodeData?.fromNodeLogistics,
                            toNodeLogistics: sourceNodeData?.toNodeLogistics,
                            logisticData: sourceNodeData?.logisticData,
                            colorBgNode: sourceNodeData?.colorBgNode,
                            countDpp: sourceNodeData?.countDpp,
                            countLogistics: sourceNodeData?.countLogistics,
                            existAssignedDpp: sourceNodeData?.existAssignedDpp,
                            existLogisticsAssignedToDpp: sourceNodeData?.existLogisticsAssignedToDpp,
                        },
                    }

                    showModal.value = true
                    emit('nodeData', selectedNode.value)

                    const element = document.getElementById(props.idPopup)

                    element?.classList.remove('hidden')
                    emit('closeModal', true)

                    if (sourceNodeData?.lockedNode || isValid_.value) {
                        showModal.value = false
                    }
                },
            },
        ],
    }

    if (targetNode) {
        menuItems.items.push(
            {
                label: t('contextMenu.addLogistics', { nodeName: sourceNodeData.name }),
                onClick: () => {
                    emit(
                        'circlePopupData',
                        prepareCirclePopupData(sourceNodeData, targetNodeData, false),
                    )
                },
            },
        )
    }

    if (targetNodeData?.steps?.length > 0) {
        menuItems.items.push({
            label: t('contextMenu.showJson', { nodeName: targetNodeData.name }),
            onClick: () => emit('showJson', targetNodeData),
        })
    }

    if (sourceNodeData?.steps?.length > 0) {
        menuItems.items.push({
            label: t('contextMenu.showJson', { nodeName: sourceNodeData.name }),
            onClick: () => emit('showJson', sourceNodeData),
        })
    }

    const targetExportedLogistics = targetNodeData?.countLogistics?.exportLogistics || 0
    const targetAssignedLogistics = targetNodeData?.countLogistics?.assignedToDpp || 0

    const sourceExportedLogistics = sourceNodeData?.countLogistics?.exportLogistics || 0
    const sourceAssignedLogistics = sourceNodeData?.countLogistics?.assignedToDpp || 0

    const targetLogisticsMenu = []
    const sourceLogisticsMenu = []

    if (targetAssignedLogistics) {
        targetLogisticsMenu.push({
            label: t('contextMenu.showAssignedLogisticDpps', { nodeName: targetNodeData.name }),
            onClick: () => {
                emit(
                    'circleInfoPopupData',
                    prepareCirclePopupData(sourceNodeData, targetNodeData, false),
                )
            },
        })
    }

    if (targetExportedLogistics) {
        targetLogisticsMenu.push({
            label: t('contextMenu.showExportedLogisticDpps', { nodeName: targetNodeData.name }),
            onClick: () => {
                emit(
                    'circleInfoPopupData',
                    prepareCirclePopupData(sourceNodeData, targetNodeData, true),
                )
            },
        })
    }

    if (sourceAssignedLogistics) {
        sourceLogisticsMenu.push({
            label: t('contextMenu.showAssignedLogisticDpps', { nodeName: sourceNodeData.name }),
            onClick: () => {
                emit(
                    'circleInfoPopupData',
                    prepareCirclePopupData(sourceNodeData, targetNodeData, false),
                )
            },
        })
    }

    if (sourceExportedLogistics) {
        sourceLogisticsMenu.push({
            label: t('contextMenu.showExportedLogisticDpps', { nodeName: sourceNodeData.name }),
            onClick: () => {
                emit(
                    'circleInfoPopupData',
                    prepareCirclePopupData(sourceNodeData, targetNodeData, true),
                )
            },
        })
    }

    const targetNotAssignedDpp = targetNodeData?.countDpp?.notAssignedDpp || 0
    const targetOngoingDpp = targetNodeData?.countDpp?.ongoingDpp || 0
    const targetExportedDpp = targetNodeData?.countDpp?.exportDpp || 0
    const targetDppLogistics = targetNodeData?.countDpp?.dppLogistics || 0
    const targetLogistics = targetNodeData?.countDpp?.logistics || 0
    const sourceNotAssignedDpp = sourceNodeData?.countDpp?.notAssignedDpp || 0
    const sourceOngoingDpp = sourceNodeData?.countDpp?.ongoingDpp || 0
    const sourceExportedDpp = sourceNodeData?.countDpp?.exportDpp || 0
    const sourceDppLogistics = sourceNodeData?.countDpp?.dppLogistics || 0
    const sourceLogistics = sourceNodeData?.countDpp?.logistics || 0

    const targetDppsMenu = []
    const sourceDppsMenu = []

    if (targetNotAssignedDpp > 0 || targetDppLogistics > 0) {
        targetDppsMenu.push({
            label: t('contextMenu.showNotAssignedDpps', { nodeName: targetNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: targetNodeData.id,
                    isExportDpp: false,
                    isConnectLogistics: false,
                    isOngoingDpp: false,
                    isNodeJson: false,
                    nodeData: targetNodeData,
                })
            },
        })
    }

    if (targetExportedDpp > 0) {
        targetDppsMenu.push({
            label: t('contextMenu.showExportedDpps', { nodeName: targetNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: targetNodeData.id,
                    isExportDpp: true,
                    isConnectLogistics: false,
                    isOngoingDpp: false,
                    isNodeJson: false,
                    nodeData: targetNodeData,
                })
            },
        })
    }

    if (targetDppLogistics > 0) {
        targetDppsMenu.push({
            label: t('contextMenu.showDppLogistics', { nodeName: targetNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: targetNodeData.id,
                    isExportDpp: false,
                    isConnectLogistics: true,
                    isOngoingDpp: false,
                    isNodeJson: false,
                    nodeData: targetNodeData,
                })
            },
        })
    }

    if (targetLogistics > 0) {
        targetDppsMenu.push({
            label: t('contextMenu.showLogistics', { nodeName: targetNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: targetNodeData.id,
                    isExportDpp: false,
                    isConnectLogistics: false,
                    isOngoingDpp: false,
                    isNodeJson: true,
                    nodeData: targetNodeData,
                })
            },
        })
    }

    if (targetOngoingDpp > 0) {
        targetDppsMenu.push({
            label: t('contextMenu.showOngoingDpps', { nodeName: targetNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: targetNodeData.id,
                    isExportDpp: false,
                    isConnectLogistics: false,
                    isOngoingDpp: true,
                    isNodeJson: false,
                    nodeData: targetNodeData,
                })
            },
        })
    }

    if (sourceNotAssignedDpp > 0 || sourceDppLogistics > 0) {
        sourceDppsMenu.push({
            label: t('contextMenu.showNotAssignedDpps', { nodeName: sourceNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: sourceNodeData.id,
                    isExportDpp: false,
                    isConnectLogistics: false,
                    isOngoingDpp: false,
                    isNodeJson: false,
                    nodeData: sourceNodeData,
                })
            },
        })
    }

    if (sourceExportedDpp > 0) {
        sourceDppsMenu.push({
            label: t('contextMenu.showExportedDpps', { nodeName: sourceNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: sourceNodeData.id,
                    isExportDpp: true,
                    isConnectLogistics: false,
                    isOngoingDpp: false,
                    isNodeJson: false,
                    nodeData: sourceNodeData,
                })
            },
        })
    }

    if (sourceOngoingDpp > 0) {
        sourceDppsMenu.push({
            label: t('contextMenu.showOngoingDpps', { nodeName: sourceNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: sourceNodeData.id,
                    isExportDpp: false,
                    isConnectLogistics: false,
                    isOngoingDpp: true,
                    isNodeJson: false,
                    nodeData: targetNodeData,
                })
            },
        })
    }

    if (sourceDppLogistics > 0) {
        sourceDppsMenu.push({
            label: t('contextMenu.showDppLogistics', { nodeName: sourceNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: sourceNodeData.id,
                    isExportDpp: false,
                    isConnectLogistics: true,
                    isOngoingDpp: false,
                    isNodeJson: false,
                    nodeData: sourceNodeData,
                })
            },
        })
    }

    if (sourceLogistics > 0) {
        sourceDppsMenu.push({
            label: t('contextMenu.showLogistics', { nodeName: sourceNodeData.name }),
            onClick: () => {
                showDppData({
                    nodeId: sourceNodeData.id,
                    isExportDpp: false,
                    isConnectLogistics: false,
                    isOngoingDpp: false,
                    isNodeJson: true,
                    nodeData: sourceNodeData,
                })
            },
        })
    }

    if (targetDppsMenu.length > 0) {
        menuItems.items.push({
            label: t('contextMenu.showDpps', { nodeName: targetNodeData.name }),
            children: [
                ...targetDppsMenu,
            ],
        })
    }

    if (sourceDppsMenu.length > 0) {
        menuItems.items.push({
            label: t('contextMenu.showDpps', { nodeName: sourceNodeData.name }),
            children: [
                ...sourceDppsMenu,
            ],
        })
    }

    if (targetLogisticsMenu.length > 0) {
        menuItems.items.push({
            label: t('contextMenu.showLogisticDpps', { nodeName: targetNodeData.name }),
            children: [
                ...targetLogisticsMenu,
            ],
        })
    }

    if (sourceLogisticsMenu.length > 0) {
        menuItems.items.push({
            label: t('contextMenu.showLogisticDpps', { nodeName: sourceNodeData.name }),
            children: [
                ...sourceLogisticsMenu,
            ],
        })
    }

    ContextMenu.showContextMenu({
        ...menuItems,
        x: params.event.clientX,
        y: params.event.clientY,
    })
}

onEdgeClick((params: any) => {
    showMenu(params)
})

onNodeClick((params: any) => {
    showMenu(params)
})

onNodeDragStop((params: any) => {
    const { node } = params

    let nodeId = node.id
    const nodeType = node.nodeType

    // Split ID to get ID
    if (nodeId.includes('/')) {
        nodeId = nodeId.split('/').pop()
    }

    if (!nodeId) {
        return
    }

    const data = {
        [`${nodeType || 'node'}Position`]: {
            x: node.position.x,
            y: node.position.y,
        },
    }

    const patchRoute = (nodeType === 'step') ? 'steps/' : 'nodes/'

/*     axiosIns.patch(`${patchRoute}${nodeId}`, data, {
        headers: {
            'Content-Type': 'application/merge-patch+json',
        },
    }) */
})

const closeModal = () => {
    showModal.value = false
    showCirclePopup_.value = false
    selectedNode.value = null
    circlePopupData.value = []
    emit('closeModal', showModal.value)
}

const getNodePosition = (node: any) => {
    const position = node.nodePosition

    return {
        x: position?.x || 0,
        y: position?.y || 0,
    }
}

const initialNodes = () => {
    const trees: any[] = []

    if (localData.value[0]?.nodes) {
        localData.value.forEach((treeItem: any) => {
            const initialNode = treeItem.nodes.map((d: any) => ({
                id: d.id,
                position: getNodePosition(d),
                type: 'custom',
                data: {
                    label: d.name,
                },
            }))

            trees.push(initialNode)
        })
    } else {
        const initialNode = localData.value.map((d: any) => {
            let parent = 0

            if (d.parents === undefined) {
                parent = d.parent?.id || 0
            } else {
                parent = d.parents?.[0]?.id || 0
            }

            return {
                id: d.id,
                position: getNodePosition(d),
                data: {
                    label: d.name,
                    count: {
                        countDpp: d.countDpp,
                        countLogistics: d.countLogistics,
                    },
                },
                type: 'custom',
                nodeType: d.nodeType,
                style: {
                    backgroundColor: d?.colorBgNode || (parent === 0 ? '#22225e' : '#65C09E'),
                    color: parent === 0 ? '#ffffff' : '#000000',
                },
            }
        })

        trees.push(initialNode)
    }

    return trees.flat()
}

const nodes = ref<any[]>(initialNodes())

const edges = computed(() => {
    const edgesLocal: any[] = []

    if (localData.value[0]?.nodes) {
        localData.value.forEach((treeItem: any) => {
            treeItem.nodes.forEach((child: any) => {
                edgesLocal.push({
                    id: `${treeItem.id}-${child.id}`,
                    source: treeItem.id,
                    target: child.id,
                    type: 'custom',
                    markerEnd: {
                        type: MarkerType.ArrowClosed,
                        color: '#22225e',
                    },
                })
            })
        })
    } else {
        localData.value.forEach((treeItem: any) => {
            if (!treeItem.childrens) {
                edgesLocal.push({
                    id: `${treeItem.id}-${treeItem.parent?.id || 0}`,
                    source: treeItem.id,
                    target: treeItem.parent?.id || 0,
                    type: 'custom',
                    markerEnd: {
                        type: MarkerType.ArrowClosed,
                        color: '#22225e',
                    },
                })
            } else {
                treeItem.childrens.forEach((child: any) => {
                    edgesLocal.push({
                        id: `${treeItem.id}-${child.id}`,
                        source: treeItem.id,
                        target: child.id,
                        type: 'custom',
                        markerEnd: {
                            type: MarkerType.ArrowClosed,
                            color: '#22225e',
                        },
                    })
                })
            }
        })
    }

    return edgesLocal
})

const layoutGraph = async function (direction, ignorePosition = false) {
    nodes.value = layout(nodes.value, edges.value, direction, ignorePosition)

    nextTick(() => {
        fitView()
    })
}

watch(() => props.data, newVal => {
    localData.value = [...newVal]
}, { immediate: true })

watch(() => localData.value, () => {
    nodes.value = initialNodes()

    layoutGraph('LR')
}, { immediate: true, deep: true })

watch(() => props.isShowPopup, newVal => {
    if (!newVal) {
        return
    }

    showModal.value = false
    showCirclePopup_.value = false
    emit('closeModal', false)
})

watch(() => props.changeOfStatus, newVal => {
    if (newVal) {
        return
    }

    const locData_ = localData.value.filter((locData: any) => locData.id === selectedNode.value.children?.id)

    const nodeData: any = locData_[0]

    if (!selectedNode.value) {
        return
    }

    selectedNode.value.children.inputs.length = 0

    nodeData.inputs.forEach((input: any) => {
        if (!selectedNode.value) {
            return
        }

        selectedNode.value.children.inputs.push({
            name: input.name,
            type: input.type,
        })
    })
})

watch(() => props.isValid, newValue => {
    isValid_.value = newValue

    showModal.value = !newValue
})

watch(() => props.showInfoNode, newValue => {
    showInfoNode_.value = newValue
})

watch(() => props.showCirclePopup,
    newData => {
        showCirclePopup_.value = newData
    },
)

const resetNodePositions = () => {
    nodes.value.forEach(node => {
        layoutGraph('LR', true)
    })
}
</script>

<template>
    <div class="tree-component">
        <VueFlow
            :nodes="nodes"
            :edges="edges"
            class="basic-flow"
            :default-viewport="{ zoom: 1.5 }"
            :min-zoom="0.2"
            :max-zoom="4"
            @nodes-initialized="layoutGraph('LR')"
        >
            <Background
                pattern-color="#aaa"
                :gap="16"
            />

            <Controls
                :show-zoom="false"
                :show-interactive="false"
                position="top-left"
            >
                <ControlButton
                    v-if="isDraggableNode"
                    title="Reset Node Positions"
                    @click="resetNodePositions"
                >
                    <PhArrowsCounterClockwise :size="32" />
                </ControlButton>
            </Controls>

            <template #edge-custom="edgeProps">
                <CustomEdge v-bind="edgeProps" />
            </template>

            <template #node-custom="nodeProps">
                <CustomNode
                    :class="{
                        nodrag: !isDraggableNode,
                    }"
                    v-bind="nodeProps.data"
                />
            </template>
        </VueFlow>

        <Popup
            v-if="showModal && selectedNode"
            :id="idPopup"
            :data="selectedNode"
            :visible="showModal"
            :class="{
                'hidden': changeOfStatus,
                'show': !changeOfStatus,
                'hes-none-tree-steps': !isNextStep && !selectedNode?.children?.steps?.length,
            }"
            @close="closeModal"
        >
            <template #default>
                <slot
                    name="popupContent"
                    :node="selectedNode"
                />
            </template>
        </Popup>

        <Popup
            v-if="showCirclePopup_"
            :id="idPopup"
            :data="circlePopupData"
            :visible="showCirclePopup_"
            :class="{
                'hidden': changeOfStatus,
                'show hes-none-tree-steps': !changeOfStatus,
            }"
            :off-close="offClosePopup"
            @close="closeModal"
        >
            <template #default>
                <slot
                    name="popupContentCircle"
                    :node="circlePopupData"
                />
            </template>
        </Popup>
    </div>
</template>

<style lang="scss">
@import "../../assets/css/tree/style.css";
@import "../../assets/css/tree/theme-default.css";
@import "../../assets/css/tree/controls.css";

.vue-flow__handle {
    opacity:0;
    height:0!important;
    width:0!important;
    min-width:0!important;
    min-height:0!important
}

.tree-component {
    height: 100vh;
    width: 100%;
}
.node {
    position: absolute;
    margin-top: -1px;
    margin-left: -1px;
    width: 170px;
    height: auto;
    min-height: 64px;
    border-radius: 10px;
    border: 1px solid #E4E2E9;

    &.error-tree {
        border: 2px solid red !important;
    }

    .node-text {
        font-size: 15px;
        margin-left: 20px;
        margin-top: 16px;
        margin-bottom: 16px;
    }

    .tooltip-content {
        position: absolute;
        background-color: #668CFF;
        color: #fff;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        z-index: 1000;
        top: -50%;
        left: 50%;
        transform: translate(-50%, -55px);

        &:after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            background: #668CFF;
            width: 15px;
            height: 15px;
        }
    }

    .lock {
        position: absolute;
        position: absolute;
        right: 10px;
        font-size: 18px;
        top: 35px;
        color: #fff;
    }

    .wrapper-json {
        z-index: 9999;
        position: absolute;
        top: 10px;
        right: 5px;

        i {
            width: 20px;
            height: 20px;
            font-size: 1.3rem;
        }
    }

    .wrapper-name {
        width: 80%;
    }

    .wrapper-info {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-direction: row-reverse;
    position: relative;
    top: 10px;
    right: 5px;

    .round {
        width: 38px;
        height: 21px;
        background-color: #66FF07;
        border-radius: 10%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #000;
        font-size: 13px;
        white-space: nowrap;

        &.count-export-dpp {
            background-color: #ff0000;
            color: #fff;
        }

        &.count-connect-logistics {
            background-color: #3498db;
            color: #fff;
        }

        &.count-ongoing-dpp {
            background-color: #ffa500;
            color: #fff;
        }
    }
}

    .node-button-g {
        display: none;
    }
}

.inset-button {
    background-color: #fff;
    text-align: center;
    line-height: 24px;
    font-size: 1.5rem;
    margin-left: 10px;
    cursor: pointer;
    box-sizing: border-box;

    .fa {
        color: #90d1b9;
        transition: 200ms ease-in-out;

        &:hover {
            color: #668CFF;
        }
    }

    &.connection-line .fa {
        vertical-align: middle;
    }
}

.inset-info {
    .round {
        width: 50px;
        height: 21px;
        background-color: #3498db;
        border-radius: 10%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 13px;
        float: right;
        margin-bottom: 5px;
        position: absolute;
        bottom: 0;
        cursor: pointer;

        &.position-bottom {
            left: 40px;
            top: 30px;
        }

        &.position-top {
            left: 40px;
            top: 0px;
            background: red;
        }
    }
}

.foreign {
    overflow: visible;
}

.box-tree {
    position: relative;
}

.vue-flow__controls {
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
}
</style>
