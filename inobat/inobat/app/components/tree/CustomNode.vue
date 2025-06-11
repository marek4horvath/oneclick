<script setup lang="ts">
import { Handle, Position } from '@vue-flow/core'

// props were passed from the slot using `v-bind="customNodeProps"`
defineProps(['label', 'count']) // eslint-disable-line vue/require-prop-types
</script>

<template>
    <div class="tree-node">
        <Handle
            type="target"
            :position="Position.Left"
        />
        <div>
            {{ label }}

            <div class="d-flex justify-end">
                <div
                    v-if="count?.countDpp?.notAssignedDpp"
                    class="count notAssignedDpp"
                >
                    {{ count?.countDpp?.notAssignedDpp }}
                </div>

                <div
                    v-if="count?.countDpp?.dppLogistics"
                    class="count dppLogistics"
                >
                    {{ count?.countDpp?.dppLogistics }}
                </div>

                <div
                    v-if="count?.countDpp?.exportDpp"
                    class="count exportDpp"
                >
                    {{ count?.countDpp?.exportDpp }}
                </div>

                <div
                    v-if="count?.countDpp?.ongoingDpp"
                    class="count ongoingDpp"
                >
                    {{ count?.countDpp?.ongoingDpp }}
                </div>
            </div>
        </div>
        <Handle
            type="source"
            :position="Position.Right"
            class="handle-source"
            :data-count-export-logistics="count?.countLogistics?.exportLogistics"
            :data-count-assigned-to-dpp="count?.countLogistics?.assignedToDpp"
            :class="{
                'count-export-logistics': (count?.countLogistics?.exportLogistics || 0) > 0,
                'count-assigned-to-dpp': (count?.countLogistics?.assignedToDpp || 0) > 0,
            }"
        />
    </div>
</template>

<style lang="scss">
.vue-flow__node {
    border-radius: 6px;
}

.handle-source {
    position: absolute;
    background: transparent !important;
    border: none !important;
    opacity: 1 !important;

    &.count-export-logistics::after,
    &.count-assigned-to-dpp::before {
        display: block;
        position: absolute;
        left: 2px;
        right: 0;
        bottom: 0;
        border-radius: 6px;
        line-height: 15px;
        text-align: center;
        width: fit-content;
        padding: 2px 10px;
        height: 19px;
    }

    &.count-export-logistics::after {
        content: attr(data-count-export-logistics);
        bottom: 10px;
        background-color: red;
    }

    &.count-assigned-to-dpp::before {
        content: attr(data-count-assigned-to-dpp);
        top: 10px;
        background-color: #3498db;
    }
}

.tree-node {
    padding: 10px;
    width: 200px;
    font-size: 12px;
    text-align: center;
    border-width: 1px;
    border-style: solid;
    border: none;

    .count {
        margin-top: 5px;
        margin-left: 5px;
        font-size: 11px;
        border-radius: 5px;
        padding: 5px 10px;
        line-height: 11px;
        color: #000;
        background-color: #66FF07;

        &.dppLogistics {
            background-color: #3498db;
            color: #fff;
        }

        &.exportDpp {
            background-color: #ff0000;
            color: #fff;
        }

        &.ongoingDpp {
            background-color: #ffa500;
            color: #000;
        }
    }
}
</style>
