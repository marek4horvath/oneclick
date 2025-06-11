<script setup lang="ts">
import ContextMenu from '@imengyu/vue3-context-menu'
import SimpleDppModal from '~/dialogs/dpps/simple-dpp.vue'
import SimpleLogisticsModal from '~/dialogs/dpps/simple-logistics.vue'
import AddDppModal from '~/dialogs/dpps/add-dpp.vue'
import AddLogisticsModal from '~/dialogs/dpps/add-logistics.vue'
import type { Companies } from "~/types/api/companies.ts"
import DetailJson from '~/dialogs/nodes/detail-json.vue'
import QrCodeModal from "~/dialogs/logistics/qr-code-modal.vue"
import AddInput from "~/dialogs/dpps/add-input.vue"
import HistoryInput from '~/dialogs/dpps/history-input.vue'

definePageMeta({
    title: 'page.supplyChains.detail.title',
    name: 'dpps-supply-chain',
    layout: 'dashboard',
    middleware: 'auth',
})

const { $listen, $event } = useNuxtApp()
const route = useRoute()
const { t } = useI18n()

const isLoading = ref<boolean>(true)
const processStore = useProcessStore()
const companyStore = useCompaniesStore()
const supplyChainStore = useSupplyChainStore()
const supplyChain = ref<any>(null)
const supplyChainId = ref<string | null>(null)
const processData = ref([])
const nodes = ref([])
const companies = ref<Companies[]>([])

const legendData = ref({
    dpps: {
        title: 'DPPs',
        data: [
            { color: '#66FF07', name: t('dppDetail.notAssignedDpp') },
            { color: '#FFA500', name: t('dppDetail.ongoingDpp') },
            { color: '#3498DB', name: t('dppDetail.dppLogisticsAssigned') },
            { color: '#FF007F', name: t('dppDetail.dppInUse') },
            { color: '#FF0000', name: t('dppDetail.exportedDpp') },
            { color: '#A020F0', name: t('dppDetail.emptyDpp') },
        ],
        isLogistics: false,
    },
    logistics: {
        title: 'Logistics DPPs',
        data: [
            { color: '#3498DB', name: t('dppDetail.logisticWaitingExported') },
            { color: '#FF0000', name: t('dppDetail.exportedLogistics') },
            { color: '#FF007F', name: t('dppDetail.inUseLogistics') },
        ],
        isLogistics: true,
    },
})

const fetchProcessNode = async () => {
    await processStore.fetchProcesses(undefined, undefined, 'node')

    processData.value = processStore.getProcesses
}

const reloadDetail = async () => {
    await supplyChainStore.fetchSupplyChainTemplate(supplyChainId.value)
    supplyChain.value = supplyChainStore.getSupplyChainTemplate

    await Promise.all(supplyChain.value.nodes.map(async (node: any) => {
        node.processColor = node.typeOfProcess?.color
        node.process = node.typeOfProcess?.name
        node.nextNode = node.children?.map((child: any) => child.name).join(', ')
        node.previousNode = node.parents?.map((parent: any) => parent.name).join(', ')
        node.subItems = node.steps.map((step: any) => {
            step.processColor = step.process?.color
            step.process = step.process?.name
            step.subItems = step?.inputs || []

            return step
        })

        companies.value = [
            ...companies.value,
            ...((node?.companiesFromProductTemplates ?? []).filter(
                (newCompany: any) => !companies.value.some((existingCompany: any) => existingCompany.id === newCompany.id),
            )),
        ]
    }))

    await companyStore.setCompaniesAssignedToSupplyChain(companies.value)

    if (supplyChain.value && route.meta.title) {
        route.meta.title = supplyChain.value.name
    }

    isLoading.value = false
}

onMounted(async () => {
    await fetchProcessNode()

    supplyChainId.value = route.params.id[1]

    await reloadDetail()
})

$listen('dppAdded', (success: boolean) => {
    if (success) {
        reloadDetail()
    }
})

$listen('logisticsAdded', (success: boolean) => {
    if (success) {
        reloadDetail()
    }
})

watch(() => supplyChain.value, newValue => {
    if (newValue) {
        nodes.value = newValue.nodes
    }
})

$listen('openMenu', ({ event, nodeData }) => {
    const menuItems = [
        {
            label: t('contextMenu.showJson', { nodeName: nodeData.name }),
            onClick: () => {
                $event('openDetailJsonModal', nodeData)
            },
        },
    ]

    menuItems.unshift(
        {
            label: t('contextMenu.addDpp'),
            onClick: () => {
                $event('openAddDppModal', { nodeData })
            },
        },
        {
            label: t('contextMenu.addLogistics'),
            onClick: () => {
                $event('openAddLogisticsModal', { nodeData, supplyChain: supplyChain.value })
            },
        },
    )

    ContextMenu.showContextMenu({
        items: menuItems,
        x: event.clientX,
        y: event.clientY,
    })
})

$listen('nodeCountClicked', data => {
    const { type, nodeData } = data

    switch (type) {
        case 'exportLogistics':
            $event('openSimpleLogisticsModal', { type: 'EXPORT_LOGISTICS', nodeData })
            break
        case 'assignedToDpp':
            $event('openSimpleLogisticsModal', { type: 'ASSIGNED_TO_DPP', nodeData })
            break
        case 'inUseLogistics':
            $event('openSimpleLogisticsModal', { type: 'IN_USE_LOGISTICS', nodeData })
            break
        case 'logistics': {
            let state = 'LOGISTICS'
            if (nodeData.children?.length === 0) {
                state = 'IN_USE'
            }

            $event('openSimpleDppModal', { type: state, nodeData })
            break
        }
        case 'exportDpp':
            $event('openSimpleDppModal', { type: 'EXPORT_DPP', nodeData })
            break
        case 'dppInUse':
            $event('openSimpleDppModal', { type: 'IN_USE', nodeData })
            break
        case 'notAssignedDpp':
            $event('openSimpleDppModal', { type: 'NOT_ASSIGNED', nodeData })
            break
        case 'ongoingDpp':
            $event('openSimpleDppModal', { type: 'ongoingDpp', nodeData })
            break
        case 'emptyDpp':
            $event('openSimpleDppModal', { type: 'emptyDpp', nodeData })
    }
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer
            fluid
            class="detail-product"
        >
            <Legend
                style="width: 100%"
                class="mt-8"
                :data="legendData"
                is-grouped
            />

            <VCard class="tree-container">
                <TreeFlow
                    :data="nodes"
                    connection-key="parents"
                    traversal="forward"
                />
            </VCard>
        </VContainer>
    </NuxtLayout>

    <AddDppModal />
    <AddLogisticsModal />
    <SimpleDppModal />
    <SimpleLogisticsModal />
    <DetailJson />
    <AddInput />
    <QrCodeModal />
    <HistoryInput />
</template>

<style lang="scss">
.v-container {
    padding: 0 30px 15px 35px;
}
.tree-container {
    width: 100%;
    margin: 2rem auto;
}
</style>
