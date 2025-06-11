<script lang="ts" setup>
import { MarkerType, VueFlow, useVueFlow } from '@vue-flow/core'
import { Background } from '@vue-flow/background'
import { ControlButton, Controls } from '@vue-flow/controls'
import ConnectionLine from './ConnectionLine.vue'
import CustomNode from './CustomNode.vue'
import { PhosphorIconArrowsClockwise } from '#components'
import { useLayout } from '@/utils/tree/useLayout'

const props = defineProps({
    data: {
        type: [Array, Object],
        required: true,
    },
    connectionKey: {
        type: String,
        default: 'parent',
    },
    traversal: {
        type: String,
        default: 'backward',
    },
    isPopulatedBySupplyChainTemplate: {
        type: Boolean,
        default: false,
    },
    hiddenCount: {
        type: Boolean,
        default: false,
    },
    disableOptions: {
        type: Boolean,
        default: false,
    },
    disableCounts: {
        type: Boolean,
        default: false,
    },
    allowPlus: {
        type: Boolean,
        default: false,
    },
    isProduct: {
        type: Boolean,
        default: false,
    },
})

const { $axios, $listen, $event } = useNuxtApp()
const { layout } = useLayout()
const { fitView, onNodeDragStop, onConnect } = useVueFlow()

const nodes = ref<any[]>([])
const edges = ref<any[]>([])

onConnect((params: any) => {
    $event('nodesConnected', params)
})

onNodeDragStop((params: any) => {
    const { node } = params

    const data = {
        [`${node.nodeType || 'node'}Position`]: {
            x: node.position.x,
            y: node.position.y,
        },
    }

    const patchRoute = (node.nodeType === 'step') ? 'steps/' : 'nodes/'

    $axios.patch(`${patchRoute}${node.id}`, data, {
        headers: {
            'Content-Type': 'application/merge-patch+json',
        },
    })
})

const getNodePosition = (node: any) => {
    let position = node.nodePosition

    if (!position) {
        position = node.stepPosition
    }

    return {
        x: position?.x || 0,
        y: position?.y || 0,
    }
}

const updateLogisticsByNode = (node: any, localNodes: any) => {
    node.children.forEach((child: any) => {
        if (!node.countLogistics || !Object.keys(node.countLogistics).length) {
            return
        }

        if (node?.fromNodeLogistics) {
            const fromNodeLogisticsArray = Object.values(node.fromNodeLogistics)
            const toNodeLogisticsArray = Array.isArray(child.toNodeLogistics) ? child.toNodeLogistics : []

            if (fromNodeLogisticsArray.some(logistic => toNodeLogisticsArray.includes(logistic))) {
                let assignedToDpp = 0
                let exportLogistics = 0
                let inUseLogistics = 0
                const assignedToDppData: any[] = []
                const exportLogisticsData: any[] = []
                const inUseLogisticsData: any[] = []

                if (node.countLogistics.assignedToDppData) {
                    Object.values(node.countLogistics.assignedToDppData).forEach(logistic => {
                        if (toNodeLogisticsArray.includes(logistic['@id'])) {
                            assignedToDpp++
                            assignedToDppData.push(logistic['@id'])
                        }
                    })
                }

                if (node.countLogistics.exportLogisticsData) {
                    Object.values(node.countLogistics.exportLogisticsData).forEach(logistic => {
                        if (toNodeLogisticsArray.includes(logistic['@id'])) {
                            exportLogistics++
                            exportLogisticsData.push(logistic['@id'])
                        }
                    })
                }

                if (node.countLogistics.inUseLogisticsData) {
                    Object.values(node.countLogistics.inUseLogisticsData).forEach(logistic => {
                        if (toNodeLogisticsArray.includes(logistic['@id'])) {
                            inUseLogistics++
                            inUseLogisticsData.push(logistic['@id'])
                        }
                    })
                }

                if (!child.countLogisticsNext) {
                    child.countLogisticsNext = {
                        assignedToDppData: [],
                        exportLogisticsData: [],
                        inUseLogisticsData: [],
                        assignedToDpp: 0,
                        exportLogistics: 0,
                        inUseLogistics: 0,
                    }
                }

                child.countLogisticsNext.assignedToDppData = [
                    ...new Set([...child.countLogisticsNext.assignedToDppData, ...assignedToDppData]),
                ]
                child.countLogisticsNext.exportLogisticsData = [
                    ...new Set([...child.countLogisticsNext.exportLogisticsData, ...exportLogisticsData]),
                ]
                child.countLogisticsNext.inUseLogisticsData = [
                    ...new Set([...child.countLogisticsNext.inUseLogisticsData, ...inUseLogisticsData]),
                ]
                child.countLogisticsNext.assignedToDpp += assignedToDpp
                child.countLogisticsNext.exportLogistics += exportLogistics
                child.countLogisticsNext.inUseLogistics += inUseLogistics

                const nextNode = localNodes.find((n: any) => n.id === child.id)
                if (nextNode) {
                    if (!nextNode.countLogisticsNext) {
                        nextNode.countLogisticsNext = { ...child.countLogisticsNext }
                    } else {
                        nextNode.countLogisticsNext.assignedToDppData = [
                            ...new Set([...nextNode.countLogisticsNext.assignedToDppData, ...assignedToDppData]),
                        ]
                        nextNode.countLogisticsNext.exportLogisticsData = [
                            ...new Set([...nextNode.countLogisticsNext.exportLogisticsData, ...exportLogisticsData]),
                        ]
                        nextNode.countLogisticsNext.inUseLogisticsData = [
                            ...new Set([...nextNode.countLogisticsNext.inUseLogisticsData, ...inUseLogisticsData]),
                        ]
                        nextNode.countLogisticsNext.assignedToDpp += assignedToDpp
                        nextNode.countLogisticsNext.exportLogistics += exportLogistics
                        nextNode.countLogisticsNext.inUseLogistics += inUseLogistics
                    }
                }

                node.countLogisticsDelete = true
            }
        }
    })

    if (node.countLogisticsDelete) {
        node.countLogistics = {
            assignedToDppData: [],
            exportLogisticsData: [],
            inUseLogisticsData: [],
            assignedToDpp: 0,
            exportLogistics: 0,
            inUseLogistics: 0,
        }
        node.countLogisticsDelete = false
    }
}

const initialNodes = () => {
    const trees: any[] = []
    const localNodes = JSON.parse(JSON.stringify(props?.data?.value || props?.data || []))

    for (let i = 0; i < localNodes.length; i++) {
        const node = localNodes[i]

        if (!node.children?.length) {
            continue
        }

        updateLogisticsByNode(node, localNodes)
    }

    localNodes.forEach(node => {
        if ((!node.children || node.children?.length === 0) && node.countDpp) {
            node.countDpp.logistics += node.countDpp.dppInUse || 0
            node.countDpp.dppInUse = 0
        }

        trees.push({
            id: node.id,
            position: getNodePosition(node),
            data: {
                label: node.name,
                counts: [
                    {
                        type: 'count-export-logistics',
                        value: node?.countLogisticsNext?.exportLogistics
                            ? node?.countLogisticsNext?.exportLogistics || 0
                            : node?.countLogistics?.exportLogistics || 0,
                    },
                    {
                        type: 'count-assigned-to-dpp',
                        value: node?.countLogisticsNext?.assignedToDpp
                            ? node?.countLogisticsNext?.assignedToDpp || 0
                            : node?.countLogistics?.assignedToDpp || 0,
                    },
                    {
                        type: 'count-in-use-logistics',
                        value: node?.countLogisticsNext?.inUseLogistics
                            ? node?.countLogisticsNext?.inUseLogistics || 0
                            : node?.countLogistics?.inUseLogistics || 0,
                    },

                    { type: 'dppInUse', value: node?.countDpp?.dppInUse || 0 },
                    { type: 'logistics', value: node?.countDpp?.logistics || 0 },
                    { type: 'exportDpp', value: node?.countDpp?.exportDpp || 0 },
                    { type: 'ongoingDpp', value: node?.countDpp?.ongoingDpp || 0 },
                    { type: 'emptyDpp', value: node?.countDpp?.emptyDpp || 0 },
                    { type: 'notAssignedDpp', value: node?.countDpp?.notAssignedDpp || 0 },
                ],
                hiddenCount: props.hiddenCount,
                nodeData: node,
                isPopulatedBySupplyChainTemplate: props.isPopulatedBySupplyChainTemplate,
            },
            type: 'custom',
            nodeType: node['@type']?.toLowerCase() || node.nodeType?.toLowerCase(),
            style: {
                backgroundColor: node?.colorBgNode || '#22225e',
                color: '#ffffff',
            },
        })
    })

    return trees.flat()
}

const initialEdges = () => {
    const edgesLocal: any[] = []

    if (props.connectionKey === 'none') {
        return edgesLocal
    }

    const localNodes = props?.data?.value || props?.data

    localNodes?.forEach(node => {
        const parents = node[props.connectionKey]

        if (!parents) {
            return
        }

        parents.forEach(parent => {
            edgesLocal.push({
                id: `${node.id}-${parent.id}`,
                source: (props.traversal === 'forward') ? parent.id : node.id,
                target: (props.traversal === 'forward') ? node.id : parent.id,
                type: 'default',
                markerEnd: {
                    type: MarkerType.ArrowClosed,
                    color: '#22225e',
                },
            })
        })
    })

    return edgesLocal
}

const layoutGraph = async function (direction, ignorePosition = false) {
    nodes.value = layout(nodes.value, edges.value, direction, ignorePosition)

    nextTick(() => {
        fitView()
    })
}

watch(() => props.data, () => {
    nodes.value = initialNodes()
    edges.value = initialEdges()

    layoutGraph('LR')
}, { immediate: true, deep: true })

const resetNodePositions = () => {
    const patchRoute = (props.isProduct) ? 'steps/' : 'nodes/'

    nodes.value.forEach(node => {
        let nodeId = node.id

        if (nodeId.includes('/')) {
            nodeId = nodeId.split('/').pop()
        }

        if (!props.disableOptions && (node.position.x || node.position.y)) {
            $axios.patch(`${patchRoute}${nodeId}`, {
                [`${node.nodeType || 'node'}Position`]: {
                    x: 0,
                    y: 0,
                },
            }, {
                headers: {
                    'Content-Type': 'application/merge-patch+json',
                },
            })
        }

        layoutGraph('LR', true)
    })
}

const linkByDrag = ref(false)

$listen('enableLinkByDrag', () => {
    linkByDrag.value = !linkByDrag.value
})

const handleBodyClick = (event: Event) => {
    const target = event.target as HTMLElement

    if (target.closest('.configuration')) {
        return
    }

    linkByDrag.value = false
}

onMounted(() => {
    document.body.addEventListener('click', handleBodyClick)
})

onBeforeUnmount(() => {
    document.body.removeEventListener('click', handleBodyClick)
})
</script>

<template>
    <div class="tree-component">
        <VueFlow
            :nodes="nodes"
            :edges="edges"
            class="basic-flow"
            :min-zoom="0.3"
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
                    title="Reset Node Positions"
                    @click="resetNodePositions"
                >
                    <PhosphorIconArrowsClockwise />
                </ControlButton>
            </Controls>

            <template #node-custom="nodeProps">
                <CustomNode
                    :node-data="nodeProps.data.nodeData"
                    :disable-options="disableOptions"
                    :disable-counts="disableCounts"
                    :allow-plus="allowPlus"
                    v-bind="nodeProps.data"
                    :link-by-drag="linkByDrag"
                />
            </template>

            <template #connection-line="{ sourceX, sourceY, targetX, targetY }">
                <ConnectionLine
                    :source-x="sourceX"
                    :source-y="sourceY"
                    :target-x="targetX"
                    :target-y="targetY"
                />
            </template>
        </VueFlow>
    </div>
</template>

<style lang="scss">
@import "../../assets/style/tree/style.css";
@import "../../assets/style/tree/theme-default.css";
@import "../../assets/style/tree/controls.css";

.vue-flow__handle {
    opacity: 0;
    min-width: 0 !important;
    min-height: 0 !important;
    width: 0 !important;
    height: 0 !important;

    &.vue-flow__handle-enabled {
        opacity: 1 !important;
        width: 15px !important;
        height: 15px !important;
        min-width: 15px !important;
        min-height: 15px !important;
    }
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
