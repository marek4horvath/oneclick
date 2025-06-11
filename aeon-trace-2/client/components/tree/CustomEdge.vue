<script setup>
import { BaseEdge, getBezierPath, useVueFlow } from '@vue-flow/core'
import { computed } from 'vue'
import SquareMarker from './SquareMarker.vue'

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    sourceX: {
        type: Number,
        required: true,
    },
    sourceY: {
        type: Number,
        required: true,
    },
    targetX: {
        type: Number,
        required: true,
    },
    targetY: {
        type: Number,
        required: true,
    },
    sourcePosition: {
        type: String,
        required: true,
    },
    targetPosition: {
        type: String,
        required: true,
    },
    source: {
        type: String,
        required: true,
    },
    target: {
        type: String,
        required: true,
    },
    data: {
        type: Object,
        required: false,
    },
    edgeLabel: {
        type: String,
        required: false,
        default: '',
    },
    markerEnd: {
        type: String,
        required: false,
    },
})

const { findNode } = useVueFlow()
const path = computed(() => getBezierPath(props))
const markerId = computed(() => `${props.id}-marker`)

const markerColor = computed(() => {
    const sourceNode = findNode(props.source)
    const targetNode = findNode(props.target)

    if (sourceNode.selected) {
        return '#ff0072'
    }

    if (targetNode.selected) {
        return '#2563eb'
    }

    return '#4a5568'
})

const markerType = computed(() => {
    const sourceNode = findNode(props.source)
    const targetNode = findNode(props.target)

    if (sourceNode.selected) {
        return 'diamond'
    }

    if (targetNode.selected) {
        return 'circle'
    }

    return 'square'
})

const getMarkerStart = () => {
    if (props.data.showMarkerStart === false) {
        return undefined
    }

    return `url(#${markerId.value})`
}

const getMarkerEnd = () => {
    if (props.data.showMarkerEnd === false) {
        return undefined
    }

    return props.markerEnd
}
</script>

<script>
export default {
    inheritAttrs: false,
}
</script>

<template>
    <BaseEdge
        :id="id"
        :path="path[0]"
        :marker-start="getMarkerStart()"
        :marker-end="getMarkerEnd()"
        :label="`${edgeLabel}`"
        :label-x="path[1]"
        :label-y="path[2]"
        label-bg-style="fill: whitesmoke"
    />

    <SquareMarker
        :id="markerId"
        :type="markerType"
        :stroke="markerColor"
        :stroke-width="1"
        :width="80"
        :height="35"
    />
</template>
