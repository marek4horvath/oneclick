<script setup lang="ts">
import AddSupplyChain from '@/dialogs/supply-chain/add-supply-chain.vue'
import EditSupplyChain from '@/dialogs/supply-chain/edit-supply-chain.vue'
import { getPage, getTotalPerPage, setPage, setUrlQueryCountRows } from '@/helpers/urlQueryCountRows'
import { debounce } from '@/helpers/debounce'
import type { TableData } from '~/types/tableData.ts'

definePageMeta({
    title: 'page.supplyChains.title',
    name: 'supplyChains',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const supplyChainStore = useSupplyChainStore()
const { isCompanyManager } = useRoleAccess()
const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const tableData = ref<TableData>({
    headers: [
        { title: t('page.supplyChains.table.headers.name'), key: 'name', sortable: true },
        { title: t('page.supplyChains.table.headers.number_of_nodes'), key: 'numberOfNodes', sortable: true },
        { title: t('page.supplyChains.table.headers.actions'), key: 'actions', width: '200px', align: 'start', sortable: false },
    ],
    idTable: 'supplyChainTable',
    totalItems: 0,
    data: [],
})

const userRoles = ref([])

const search = ref({
    supplyChain: '',
})

const page = ref(1)
const itemsPerPage = ref(20)
const isLoading = ref<boolean>(true)

const currentCountRows = route.query.countRows
    ? JSON.parse(decodeURIComponent(route.query.countRows as string))
    : {}

const perPage = ref<{ [key: string]: number }>({
    tableSupplyChain: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableSupplyChain : getTotalPerPage('tableSupplyChain') ?? 20,
})

const updateSupplyChainsTable = () => {
    tableData.value.totalItems = supplyChainStore.supplyChainTemplatesItemsCount
    tableData.value.data = supplyChainStore.supplyChainTemplates
}

const fetchSupplyChainData = (pageLocal?: number, itemsPerPageLocal?: number, orderName?: string, orderValue?: string, searchName?: string) => {
    supplyChainStore.fetchSupplyChainTemplates(pageLocal, itemsPerPageLocal, orderName, orderValue, searchName)
        .then(() => {
            isLoading.value = false
            updateSupplyChainsTable()
        })
}

const debouncedFetchSupplyChainData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]

    fetchSupplyChainData(pagination.page, pagination.itemsPerPage, sort?.key, sort?.order, pagination.search)
}, 500)

const getPaginationDataSupplyChain = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableSupplyChain', router, route)
    setPage('tableSupplyChain', pagination.page)
    tableData.value.data = []
    perPage.value.tableSupplyChain = pagination.itemsPerPage
    debouncedFetchSupplyChainData(pagination)

    isLoading.value = false
}

const detailSupplyChain = (item: any) => {
    navigateTo(`/supply-chains/detail/${item.id}`)
}

const handleRowClick = (item: any) => {
    navigateTo(`/supply-chains/detail/${item.id}`)
}

const openAddSupplyChainModal = () => {
    $event('addSupplyChain')
}

const openEditSupplyChainModal = item => {
    $event('editSupplyChain', item)
}

const removeSupplyChain = async item => {
    await supplyChainStore.removeSupplyChainTemplate(item.id)
    fetchSupplyChainData(page.value, itemsPerPage.value)
}

$listen('updateSupplyChainTableAfterEdit', () => {
    updateSupplyChainsTable()
})

$listen('handleSupplyChainSubmitted', async () => {
    const pageTable = getPage('tableSupplyChain') || 20
    const itemsPerPageTable = getTotalPerPage('tableSupplyChain') || 20

    await fetchSupplyChainData(pageTable, itemsPerPageTable)
})

onMounted(() => {
    const authStore = useAuthStore()

    userRoles.value = authStore.getRoles
})
</script>

<template>
    <NuxtLayout>
        <VContainer fluid>
            <TableData
                key="supplyChainTable"
                :search="search.supplyChain"
                :items="tableData"
                :is-delete="false"
                :loading="isLoading"
                :per-page="perPage.tableSupplyChain"
                @pagination="getPaginationDataSupplyChain"
                @set-row-data="handleRowClick"
            >
                <template #table-title>
                    <div class="me-6 w-100 d-flex align-center justify-space-between">
                        <div class="header-title">
                            <h3>
                                {{ t('supplyChains.title') }}
                            </h3>
                        </div>

                        <div class="d-flex">
                            <div class="search-wrap me-4">
                                <VTextField
                                    v-model="search.supplyChain"
                                    :label="t('supplyChains.search')"
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

                            <div class="actions">
                                <VBtn
                                    class="text-uppercase"
                                    color="#26A69A"
                                    variant="flat"
                                    size="large"
                                    @click="openAddSupplyChainModal"
                                >
                                    {{ t('supplyChains.addSupplyChain') }}
                                </VBtn>
                            </div>
                        </div>
                    </div>
                </template>

                <template #table-actions="{ item }">
                    <div
                        v-if="item"
                        class="actions"
                    >
                        <VBtn
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            :title="$t('actionsTablesTitle.detail')"
                            @click="detailSupplyChain(item)"
                        >
                            <PhosphorIconEye
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>
                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            :title="$t('actionsTablesTitle.edit')"
                            @click="openEditSupplyChainModal(item)"
                        >
                            <PhosphorIconPencil
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>
                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            :title="$t('actionsTablesTitle.delete')"
                            @click="removeSupplyChain(item)"
                        >
                            <PhosphorIconTrash
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>
                    </div>
                </template>
            </TableData>
        </VContainer>

        <AddSupplyChain />
        <EditSupplyChain />
    </NuxtLayout>
</template>
