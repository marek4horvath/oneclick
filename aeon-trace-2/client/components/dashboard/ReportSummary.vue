<script setup lang="ts">
import Draggable from 'vuedraggable'
import BarChart from '@/components/graph/BarChart.vue'

const { t } = useI18n()

const widgets = ref([
    { name: t('dashboard.widgetName.productionCost'), widgetTitle: t('dashboard.widgetTitle.decisionTimeSave'), widgetValue: '9:82 min' },
    { name: t('dashboard.widgetName.workingHoursPerProducts'), widgetTitle: t('dashboard.widgetTitle.profitFromAnalyze'), widgetValue: '2.300k' },
    { name: t('dashboard.widgetName.airPollution'), widgetTitle: t('dashboard.widgetTitle.co2Emissions'), widgetValue: '04' },
    { name: t('dashboard.widgetName.cycleTimePerProduct'), widgetTitle: t('dashboard.widgetTitle.producedProducts'), widgetValue: '32' },
    { name: t('dashboard.widgetName.illustrations'), widgetTitle: t('dashboard.widgetTitle.qualityInspection'), widgetValue: '28' },
    { name: t('dashboard.widgetName.promotionalLp'), widgetTitle: t('dashboard.widgetTitle.pricePerProduct'), widgetValue: '56.26 €' },
])
</script>

<template>
    <div class="report-summary">
        <VRow class="report-summary-widget-row">
            <VCard
                class="sort"
                variant="flat"
            >
                <Draggable
                    v-model="widgets"
                    class="mt-8"
                    group="reports"
                    handle=".drag-handle"
                    item-key="report"
                >
                    <template #item="{ element }">
                        <div class="sort-item">
                            <span class="report-summary-widget-name">{{ element.name }}</span>
                            <PhosphorIconDotsThree
                                cols="3"
                                class="drag-handle"
                            />
                        </div>
                    </template>
                </Draggable>
            </VCard>
            <VCard
                class="sort-widgets"
                variant="flat"
            >
                <Draggable
                    v-model="widgets"
                    class="sort-widgets-drag"
                    group="reports"
                    item-key="name"
                >
                    <template #item="{ element }">
                        <VCard
                            variant="flat"
                            class="widget-card"
                        >
                            <h4 class="widget-card-title">
                                {{ element.widgetTitle }}
                            </h4>
                            <p class="widget-card-content">
                                {{ element.widgetValue }}
                            </p>
                        </VCard>
                    </template>
                </Draggable>
            </VCard>
            <VCard
                variant="flat"
                class="report-graph"
            >
                <div>
                    <p class="title">
                        {{ $t('dashboard.wastesPerEachStep') }}
                    </p>
                    <p class="value">
                        2.579
                        <span class="appended-text"> wastes</span>
                    </p>
                </div>
                <BarChart
                    :data="[30, 20, 45, 25, 35, 50, 15]"
                    class="bar-chart"
                />
            </VCard>
            <VCard
                variant="flat"
                class="daily-report"
            >
                <div class="title-container">
                    <span class="title">Daily Report</span>
                    <PhosphorIconFileCsv :size="25" />
                </div>
                <div class="content">
                    <VRow>
                        <VCol cols="7">
                            <p class="key">
                                Profit
                            </p>
                            <p class="value">
                                2000 k
                            </p>
                        </VCol>
                        <VCol cols="5">
                            <p class="key">
                                Wastes
                            </p>
                            <p class="value">
                                50Kg
                            </p>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol cols="7">
                            <p class="key">
                                Alarms
                            </p>
                            <p class="value">
                                2
                            </p>
                        </VCol>
                        <VCol cols="5">
                            <p class="key">
                                Products
                            </p>
                            <p class="value">
                                127
                            </p>
                        </VCol>
                    </VRow>
                </div>
                <div class="summary">
                    <span class="title">Environmental status</span>
                    <span class="status">Good</span>
                </div>
            </VCard>
        </VRow>
    </div>
</template>
