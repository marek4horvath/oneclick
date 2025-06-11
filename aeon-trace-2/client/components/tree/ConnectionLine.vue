<script setup lang="ts">
interface CustomConnectionLineProps {
    sourceX: number
    sourceY: number
    targetX: number
    targetY: number
}

const props = defineProps<CustomConnectionLineProps>()
</script>

<template>
    <g>
        <path
            class="animated"
            fill="none"
            stroke="#222"
            :stroke-width="1.5"
            :d="`M${props.sourceX},${props.sourceY} C ${props.sourceX} ${props.targetY} ${props.sourceX} ${props.targetY} ${props.targetX},${props.targetY}`"
        />
        <circle
            :cx="props.targetX"
            :cy="props.targetY"
            fill="#fff"
            :r="3"
            stroke="#222"
            :stroke-width="1.5"
        />
    </g>
</template>

<style lang="scss">
.vue-flow__edge.animated path {
     stroke-dasharray: 5;
     animation: dashdraw 0.5s linear infinite;
   }

.vue-flow__edge.animated path.vue-flow__edge-interaction {
     stroke-dasharray: none;
     animation: none;
   }
</style>
