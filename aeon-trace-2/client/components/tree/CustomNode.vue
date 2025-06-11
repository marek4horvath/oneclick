<script setup lang="ts">
import { Handle, Position } from '@vue-flow/core'
import { isString } from 'lodash'

const props = defineProps({
    label: {
        type: String,
        default: '',
    },
    nodeData: {
        type: Object,
        required: true,
    },
    counts: {
        type: Array,
        required: false,
        default: () => [],
    },
    isPopulatedBySupplyChainTemplate: {
        type: Boolean,
        default: false,
        required: false,
    },
    disableOptions: {
        type: Boolean,
        default: false,
        required: false,
    },
    disableCounts: {
        type: Boolean,
        default: false,
        required: false,
    },
    allowPlus: {
        type: Boolean,
        default: false,
        required: false,
    },
    linkByDrag: {
        type: Boolean,
        default: false,
        required: false,
    },
})

const { isCompanyManager } = useRoleAccess()
const { $event } = useNuxtApp()

const formattedProcessName = computed(() => {
    const process = props.nodeData.process

    if (!process) {
        return ''
    }

    const processName = isString(process) ? process : process.name

    return processName
        ?.toUpperCase()
        ?.replace(/ /g, '_')
        ?.replace(/_/g, '_\u200B')
})

const processColor = computed(() => {
    return props.nodeData.processColor
})

const isNodeTableOpen = ref<boolean>(false)

const openMenu = (ev: PointerEvent) => {
    if (props.disableOptions) {
        return
    }
    $event('openMenu', { event: ev, nodeData: props.nodeData })
}

const handleCountClick = (event: Event, type: string) => {
    event.stopPropagation()
    $event('nodeCountClicked', { type, nodeData: props.nodeData })
}

const handleOpenTableChange = event => {
    isNodeTableOpen.value = event.target.checked
    $event('openNodeTable', { node: props.nodeData, checked: isNodeTableOpen.value })
}

const enableLinkByDrag = () => {
    $event('enableLinkByDrag')
}

const countLogistics = computed(() => {
    const exportLogistics = props.counts.find(c => c.type === 'count-export-logistics')
    const assignedToDpp = props.counts.find(c => c.type === 'count-assigned-to-dpp')
    const inUseLogistics = props.counts.find(c => c.type === 'count-in-use-logistics')

    return {
        exportLogistics,
        assignedToDpp,
        inUseLogistics,
    }
})
</script>

<template>
    <div
        class="tree-node"
        :style="{ backgroundColor: processColor }"
        @click="openMenu"
    >
        <Handle
            type="target"
            :position="Position.Left"
            class="handle-source"
            :data-count-export-logistics="countLogistics.exportLogistics?.value"
            :data-count-assigned-to-dpp="countLogistics.assignedToDpp?.value"
            :data-count-in-use-logistics="countLogistics.inUseLogistics?.value"
            :class="{
                'count-export-logistics': !disableCounts && (countLogistics.exportLogistics?.value || 0) > 0,
                'count-assigned-to-dpp': !disableCounts && (countLogistics.assignedToDpp?.value || 0) > 0,
                'count-in-use-logistics': !disableCounts && (countLogistics.inUseLogistics?.value || 0) > 0,
                'vue-flow__handle-enabled': linkByDrag,
            }"
        >
            <div
                v-if="!disableCounts && (countLogistics.assignedToDpp?.value || 0) > 0"
                class="count-assigned-to-dpp-block"
                @click="handleCountClick($event, 'assignedToDpp')"
            />
            <div
                v-if="!disableCounts && (countLogistics.exportLogistics?.value || 0) > 0"
                class="count-export-logistics"
                @click="handleCountClick($event, 'exportLogistics')"
            />

            <div
                v-if="!disableCounts && (countLogistics.inUseLogistics?.value || 0) > 0"
                :data-count-in-use-logistics="countLogistics.inUseLogistics?.value"
                class="count-in-use-logistics"
                :class="[{ 'no-export-logistics': countLogistics.exportLogistics?.value === 0 }]"
                @click="handleCountClick($event, 'inUseLogistics')"
            />
        </Handle>

        <div class="label-container">
            <div
                v-if="isPopulatedBySupplyChainTemplate"
                class="checkbox"
            >
                <input
                    :id="nodeData.id"
                    type="checkbox"
                    class="d-none display-content-checkbox-input"
                    @change="handleOpenTableChange"
                >
                <label
                    :for="nodeData.id"
                    class="display-content-checkbox"
                >
                    <PhosphorIconCheck
                        v-if="isNodeTableOpen"
                        :size="12"
                    />
                </label>
            </div>
            <div class="actions">
                <PhosphorIconPlus
                    v-if="allowPlus && isCompanyManager()"
                    class="configuration"
                    :size="20"
                    @click.stop="enableLinkByDrag"
                />
                <PhosphorIconDotsThree
                    v-if="!disableOptions"
                    class="configuration"
                    :size="20"
                    @click="openMenu"
                />
            </div>

            <div class="label_process_name">
                {{ formattedProcessName }}
            </div>

            <div class="label">
                {{ label }}
            </div>
        </div>
        <div
            v-if="counts.length > 0 && !disableCounts"
            class="counts"
        >
            <span
                v-for="count in counts"
                :key="count.type"
            >
                <span
                    v-if="count.value > 0 && count.type !== 'count-export-logistics' && count.type !== 'count-assigned-to-dpp' && count.type !== 'count-in-use-logistics'"
                    :class="`count ${count.type}`"
                    @click="handleCountClick($event, count.type)"
                >
                    {{ count.value }}
                </span>
            </span>
        </div>

        <Handle
            type="source"
            :position="Position.Right"
            :class="{
                'vue-flow__handle-enabled': linkByDrag,
            }"
        />
    </div>
</template>

<style lang="scss">
.vue-flow__node {
    border-radius: 6px;
}

.display-content-checkbox {
    margin-top: 2px;
    margin-inline-start: 5px;
    margin-inline-end: 0;
    width: 16px;
    height: 16px;
    border: 1px solid;
    border-radius: 2px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    top: 0;
    left: 0;

    .v-icon {
        color: white;
        opacity: 0;
        transition: opacity 0.2s;
    }
}

.handle-source {
    position: absolute;
    background: transparent !important;
    border: none !important;
    opacity: 1 !important;

    &.count-export-logistics::after,
    &.count-assigned-to-dpp::before,
    &.count-in-use-logistics::after {
        display: block;
        position: absolute;
        left: -30px;
        right: 0;
        bottom: 0;
        border-radius: 50%;
        line-height: 20px;
        text-align: center;
        width: fit-content;
        padding: 2px 9px;
        height: 25px;
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

    .count-assigned-to-dpp-block, .count-export-logistics {
        width: 27px;
        height: 19px;
        cursor: pointer;
        position: absolute;
        z-index: 9;
        top: 10px;
        left: -30px;
    }

    .count-in-use-logistics::after {
        content: attr(data-count-in-use-logistics);
        background-color: #FF007F;
        display: block;
        position: absolute;
        right: 0;
        bottom: 0;
        border-radius: 50%;
        line-height: 20px;
        text-align: center;
        width: fit-content;
        padding: 2px 9px;
        height: 25px;
        cursor: pointer;
        z-index: 9;
        top: -35px;
        left: -57px;
    }

    .count-in-use-logistics.no-export-logistics::after {
        left: -32px;
    }

    .count-export-logistics, .count-in-use-logistics {
        top: -29px;
    }
}

.tree-node {
    padding: 10px;
    width: 200px;
    font-size: 12px;
    text-align: center;
    border: 1px none;
    border-radius: 6px;

    .actions {
        display: flex;
        justify-content: end;
        position: absolute;
        top: 10px;
        right: 10px;
        gap: 5px;
        cursor: pointer;
    }
    .checkbox {
        display: flex;
        justify-content: start;
        position: absolute;
        top: 10px;
        left: 10px;
        gap: 5px;
        cursor: pointer;
    }

    .label_process_name {
        text-align: left;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .counts {
        margin-top: 1rem;
        display: flex;
    }

    .count {
        margin-top: 5px;
        margin-right: 5px;
        font-size: 11px;
        border-radius: 5px;
        padding: 5px 10px;
        text-align: center;
        line-height: 11px;
        color: #000;
        background-color: #66FF07;
        cursor: pointer;

        &.logistics {
            background-color: #3498db;
            color: #fff;
        }

        &.dppInUse {
            background-color: #FF007F;
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

        &.count-export-logistics {
            background-color: red;
            color: #fff;
        }

        &.count-assigned-to-dpp {
            background-color: #3498db;
            color: #fff;
        }

        &.emptyDpp {
            background-color: #A020F0;
            color: #fff;
        }
    }

    .label-container {
        margin-top: 2rem;

        .label {
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 1rem;
            margin-bottom: 1rem;
            line-height: 1.2rem;
            text-align: left;
        }
    }

    .checkbox {
        margin-left: auto;

        .v-input {
            margin-top: 0px;
        }
    }
}
</style>
