<script setup lang="ts">
const props = defineProps({
    data: {
        type: [Array, Object] as PropType<any[] | Record<string, any>>,
        required: true,
    },
    isGrouped: {
        type: Boolean,
        default: false,
    },
    isSquare: {
        type: Boolean,
        default: false,
    },
    legendName: {
        type: String,
        default: '',
    },
})

function arrayChunk<T>(array: T[], size: number): T[][] {
    return Array.from({ length: Math.ceil(array.length / size) }, (_, i) =>
        array.slice(i * size, i * size + size),
    )
}

const chunkedData = computed(() => arrayChunk(props.data, 5))
</script>

<template>
    <div class="legend-wrapper">
        <VRow>
            <VCol>
                <div v-if="!isGrouped">
                    <VCard class="box box-legend">
                        <div class="legend">
                            <span class="title">{{ `${$t("legend.title")}: ${legendName}` }}</span>
                            <VRow
                                v-for="(row, rowIndex) in chunkedData"
                                :key="rowIndex"
                                dense
                            >
                                <VCol
                                    v-for="(coll) in row"
                                    :key="coll.id"
                                >
                                    <div class="mark-container">
                                        <div
                                            :class="isSquare ? 'square' : 'mark'"
                                            :style="{ backgroundColor: coll.color }"
                                        />
                                        <span class="text">{{ coll.name }}</span>
                                    </div>
                                </VCol>

                                <template v-if="5 - row.length > 0">
                                    <VCol
                                        v-for="n in 5 - row.length"
                                        :key="`empty-${n}`"
                                    />
                                </template>
                            </VRow>
                        </div>
                    </VCard>
                </div>
                <div v-else>
                    <span class="title">{{ $t("legend.title") }}</span>
                    <VCard
                        v-for="group in data"
                        :key="group.id"
                        class="box box-legend mt-5"
                    >
                        <div class="legend">
                            <span class="sub-title">{{ group.title }}</span>

                            <VRow
                                v-for="(row, rowIndex) in arrayChunk(group.data, 5)"
                                :key="rowIndex"
                                dense
                            >
                                <VCol
                                    v-for="(coll) in row"
                                    :key="coll.id"
                                >
                                    <div class="mark-container">
                                        <div
                                            :class="group.isLogistics ? 'mark' : 'square'"
                                            :style="{ backgroundColor: coll.color }"
                                        />
                                        <span class="text">{{ coll.name }}</span>
                                    </div>
                                </VCol>

                                <template v-if="5 - row.length > 0">
                                    <VCol
                                        v-for="n in 5 - row.length"
                                        :key="`empty-${n}`"
                                    />
                                </template>
                            </VRow>
                        </div>
                    </VCard>
                </div>
            </VCol>
        </VRow>
    </div>
</template>

<style scoped lang="scss">
.legend-wrapper {
    .title {
        font-size: 1.2rem;
        margin-bottom: 10px;
        display: block;
        color: #26A69A;
        line-height: 1.2rem;
    }

    .box-legend {
        height: auto;
        width: 100% !important;

        .legend {
            padding: 30px;

            .sub-title {
                margin-right: 1rem;
                margin-top: 1rem;
                font-size: 1.2rem;
                margin-bottom: 1rem;
                display: block;
                color: #26A69A;
                line-height: 1.2rem;
            }

            .mark-container {
                display: flex;
                align-items: center;
                gap: 0.8rem;
                margin: 0.5rem;
            }

            .mark {
                width: 0.8rem;
                height: 0.8rem;
                border-radius: 50%;
            }

            .square {
                width: 0.8rem;
                height: 0.8rem;
                border-radius: 2px;
            }

            .text {
                flex: 1;
                text-align: left;
                font-size: 0.9rem;
            }
        }
    }
}
</style>
