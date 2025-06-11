<script setup lang="ts">
import { PhosphorIconEye } from '#components'
import type { TableData } from '~/types/tableData.ts'
import type { SupplyChain } from '~/types/api/supplyChains.ts'
import { getTotalPerPage, setUrlQueryCountRows } from '~/helpers/urlQueryCountRows.ts'
import { debounce } from '~/helpers/debounce.ts'

definePageMeta({
    title: 'page.dataSharingPolicy.title',
    name: 'index-data-sharing-policy',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const supplyChainStore = useSupplyChainStore()
const isLoading = ref<boolean>(true)

const supplyChains = ref<SupplyChain[]>()

const search = ref({
    supplyChain: '',
})

const currentCountRows = route.query.countRows
    ? JSON.parse(decodeURIComponent(route.query.countRows as string))
    : {}

const perPage = ref<{ [key: string]: number }>({
    tableSupply: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableSupply : getTotalPerPage('tableSupply') ?? 20,
})

const supplyChainTable = ref<TableData>({
    headers: [
        { key: '', title: '' },
        { key: 'name', title: t('supplyTableHeader.supplyName'), sortable: true },
        { key: 'numberOfNodes', title: t('supplyTableHeader.numberOfNode'), sortable: true },
        { key: 'actions', title: t('productTableHeader.actions'), sortable: false },
    ],
    idTable: 'supplyChainTable',
    totalItems: 0,
    data: [],
})

const updateSupplyChainTable = () => {
    supplyChainTable.value.totalItems = supplyChainStore.getSupplyChainTemplatesItemsCount
    supplyChainTable.value.data = supplyChainStore.supplyChainTemplates
}

const fetchSupplyChainData = async (page?: number, itemsPerPage?: number, orderName?: string, orderValue?: string, searchName?: string) => {
    try {
        await supplyChainStore.fetchSupplyChainTemplates(page, itemsPerPage, orderName, orderValue, searchName)
    } finally {
        isLoading.value = false
    }
    supplyChains.value = supplyChainStore.getSupplyChainTemplates
    updateSupplyChainTable()
}

const debouncedFetchProductData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]

    fetchSupplyChainData(pagination.page, pagination.itemsPerPage, sort?.key, sort?.order, pagination.search)
}, 500)

const getPaginationDataSupplyChain = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableSupply', router, route)
    supplyChainTable.value.data = []
    perPage.value.tableSupply = pagination.itemsPerPage
    debouncedFetchProductData(pagination)

    isLoading.value = false
}

const handleRowClick = (rowData: any) => {
    router.push(`/data-sharing-policy/detail/${rowData.id}`)
}
</script>

<template>
    <NuxtLayout>
        <VContainer fluid>
            <div class="supply-chain">
                <TableData
                    key="supplyChainTable"
                    :search="search.supplyChain"
                    :items="supplyChainTable"
                    :is-delete="false"
                    :loading="isLoading"
                    :per-page="perPage.tableSupply"
                    @pagination="getPaginationDataSupplyChain"
                    @set-row-data="handleRowClick"
                >
                    <template #table-title>
                        <div class="w-100 d-flex align-center justify-space-between">
                            <div class="header-title">
                                <h3>
                                    {{ t('supply.template') }}
                                </h3>
                            </div>

                            <div class="d-flex">
                                <div class="search-wrap">
                                    <VTextField
                                        v-model="search.supplyChain"
                                        :label="t('supply.search')"
                                        hide-details
                                        class="search"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                    >
                                        <template #append-inner>
                                            <PhosphorIconMagnifyingGlass :size="24" />
                                        </template>
                                    </VTextField>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template #table-actions="{ item }">
                        <div
                            v-if="item"
                            class="actions"
                            :title="$t('actionsTablesTitle.detail')"
                        >
                            <PhosphorIconEye
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                                class="cursor-pointer me-2"
                                @click="handleRowClick(item)"
                            />
                        </div>
                    </template>
                </TableData>
            </div>
        </VContainer>
    </NuxtLayout>
</template>
