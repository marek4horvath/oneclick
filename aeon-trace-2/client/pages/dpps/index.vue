<script setup lang="ts">
import type { TableData } from '@/types/tableData'
import QrCodeModal from '~/dialogs/logistics/qr-code-modal.vue'
import EditSupplyChain from '@/dialogs/supply-chain/edit-supply-chain.vue'
import EditDpp from '~/dialogs/dpps/edit-dpp.vue'
import { debounce } from '@/helpers/debounce'
import { formattedDate } from '@/helpers/formattedDate'
import type { SupplyChain } from "~/types/api/supplyChains.ts"
import { getPage, getTotalPerPage, setPage, setUrlQueryCountRows } from '@/helpers/urlQueryCountRows'

definePageMeta({
    title: 'page.dpps.title',
    name: 'index-dpps',
    layout: 'dashboard',
    middleware: 'auth',
})

const { $event, $listen, $swal } = useNuxtApp()
const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const isLoading = ref<boolean>(true)
const authStore = useAuthStore()
const supplyChainStore = useSupplyChainStore()
const dppStore = useDppStore()
const logisticsStore = useLogisticsStore()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

const legendData = ref({
    dpps: [
        { color: '#66FF07', name: t('dppDetail.notAssignedDpp') },
        { color: '#FFA500', name: t('dppDetail.ongoingDpp') },
        { color: '#3498DB', name: t('dppDetail.dppLogisticsAssigned') },
        { color: '#FF007F', name: t('dppDetail.dppInUse') },
        { color: '#FF0000', name: t('dppDetail.exportedDpp') },
        { color: '#A020F0', name: t('dppDetail.emptyDpp') },
    ],
    logistics: [
        { color: '#3498DB', name: t('dppDetail.logisticWaitingExported') },
        { color: '#FF0000', name: t('dppDetail.exportedLogistics') },
        { color: '#FF007F', name: t('dppDetail.inUseLogistics') },
    ],
})

const search = ref({
    supplyChain: '',
    dpp: '',
    logistics: '',
})

const pages = ref({
    tableSupplyChain: 1,
    tableDpp: 1,
    tableLogistics: 1,
})

const currentCountRows = route.query.countRows
    ? JSON.parse(decodeURIComponent(route.query.countRows as string))
    : {}

const perPage = ref<{ [key: string]: number }>({
    tableSupplyChain: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableSupplyChain : getTotalPerPage('tableSupplyChain') ?? 20,
    tableDpp: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableDpp : getTotalPerPage('tableDpp') ?? 20,
    tableLogistics: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableLogistics : getTotalPerPage('tableLogistics') ?? 20,
})

// Supply Chain Table
const supplyChainTable = ref<TableData>({
    headers: [
        { key: 'name', title: t('supplyTableHeader.supplyName'), sortable: true },
        { key: 'numberOfNodes', title: t('supplyChainTableHeader.numberOfNode'), sortable: true },
        { key: 'actions', title: t('supplyChainTableHeader.actions'), width: '200px', align: 'start', sortable: false },
    ],
    idTable: 'supplyChainTable',
    totalItems: 0,
    data: [],
})

// DPP Table
const dppTable = ref<TableData>({
    headers: [
        { key: 'tag', title: '', sortable: false },
        { key: 'name', title: t('dppTableHeader.supplyChainNodeName'), sortable: true },
        { key: 'numberOfInputs', title: t('dppTableHeader.numberOfInputs'), sortable: true },
        { key: 'createdAt', title: t('dppTableHeader.createdAt'), sortable: true },
        { key: 'tsaVerifiedAt', title: t('dppTableHeader.tsaVerified'), sortable: true },
        { key: 'userData', title: t('dppTableHeader.createdBy'), sortable: true },
        { key: 'actions', title: t('dppTableHeader.actions'), width: '201px', align: 'start', sortable: false },
    ],
    idTable: 'dppTable',
    totalItems: 0,
    data: [],
})

watch(() => dppStore.totalItems, () => {
    dppTable.value.totalItems = dppStore.totalItems
})

watch(() => dppStore.dpps, () => {
    dppTable.value.data = []
    dppTable.value.data = dppStore.dpps.map((dpp: any) => ({ ...dpp, createdAt: formattedDate(dpp?.createdAt) }))
})

// Logistics Table
const logisticsTable = ref<TableData>({
    headers: [
        { key: 'tag', title: '', sortable: false },
        { key: 'name', title: t('logistics.logistics'), sortable: true },
        { key: 'numberOfInputs', title: t('products.numberOfInputs'), sortable: true },
        { key: 'createdAt', title: t('dpps.createdAt'), sortable: true },
        { key: 'tsaVerifiedAt', title: t('dppTableHeader.tsaVerified'), sortable: true },
        { key: 'userData', title: t('dppTableHeader.createdBy'), sortable: true },
        { key: 'actions', title: t('logisticsTableHeader.actions'), width: '202px', align: 'start', sortable: false },
    ],
    idTable: 'logisticsTable',
    totalItems: 0,
    data: [],
})

watch(() => logisticsStore.totalItems, () => {
    logisticsTable.value.totalItems = logisticsStore.totalItems
})

watch(() => logisticsStore.logistics, () => {
    logisticsTable.value.data = logisticsStore.logistics?.map((logistic: any) => ({ ...logistic, createdAt: formattedDate(logistic?.createdAt) }))
})

const debouncedFetchSupplyChainData = debounce((pagination: any) => {
    isLoading.value = true

    const sort = pagination?.sortBy?.[0]

    supplyChainStore.fetchSupplyChainTemplates(pagination.page, pagination.itemsPerPage, sort?.key, sort?.order, pagination.search)
        .then(() => {
            supplyChainTable.value.totalItems = supplyChainStore.supplyChainTemplatesItemsCount
            supplyChainTable.value.data = supplyChainStore.supplyChainTemplates
            isLoading.value = false
            pages.value.tableSupplyChain = pagination.page
        })
}, 500)

const getPaginationDataSupplyChain = (pagination: any) => {
    setPage('tableSupplyChain', pagination.page)
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableSupplyChain', router, route)
    perPage.value.tableSupplyChain = pagination.itemsPerPage
    debouncedFetchSupplyChainData(pagination)
}

const debouncedFetchDppData = debounce((pagination: any) => {
    isLoading.value = true

    const sort = pagination?.sortBy?.[0] || { key: 'createdAt', order: 'desc' }

    dppStore.fetchDppsListing(pagination.page, pagination.itemsPerPage, sort?.key, sort?.order, pagination.search)
        .then(() => {
            dppTable.value.totalItems = dppStore.dppsItemsCount
            dppTable.value.data = dppStore.dpps.map((dpp: any) => ({ ...dpp, createdAt: formattedDate(dpp?.createdAt) }))
            isLoading.value = false
            pages.value.tableDpp = pagination.page
        })
}, 500)

const getPaginationDataDpp = (pagination: any) => {
    setPage('tableDpp', pagination.page)
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableDpp', router, route)
    perPage.value.tableDpp = pagination.itemsPerPage
    debouncedFetchDppData(pagination)
}

const debouncedFetchLogisticsData = debounce((pagination: any) => {
    isLoading.value = true

    const sort = pagination?.sortBy?.[0]

    logisticsStore.fetchLogisticsListing(pagination.page, pagination.itemsPerPage, pagination.search, sort?.key, sort?.order)
        .then(() => {
            logisticsTable.value.totalItems = logisticsStore.logisticsTotalItems
            logisticsTable.value.data = logisticsStore.logistics?.map((logistic: any) => ({ ...logistic, createdAt: formattedDate(logistic?.createdAt) }))
            isLoading.value = false
            pages.value.tableLogistics = pagination.page
        })
}, 500)

const getPaginationDataLogistics = (pagination: any) => {
    setPage('tableLogistics', pagination.page)
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableLogistics', router, route)
    perPage.value.tableLogistics = pagination.itemsPerPage
    debouncedFetchLogisticsData(pagination)
}

const handleDownloadTsa = (id: string, type: string) => {
    const token = authStore.getAccessToken
    let url: string

    if (type === 'ProductStep') {
        url = `${backendUrl.value}/api/product-step-tsa-download/${id}`
    } else if (type === 'Dpp') {
        url = `${backendUrl.value}/api/dpp-tsa-download/${id}`
    } else {
        url = `${backendUrl.value}/api/logistics-tsa-download/${id}`
    }

    const link = document.createElement('a')

    link.href = `${url}?token=${token}`
    link.download = ''
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

const removeSupplyChain = async (supplyChain: SupplyChain) => {
    const response = await supplyChainStore.removeSupplyChainTemplate(supplyChain.id)

    if (response) {
        await supplyChainStore.fetchSupplyChainTemplates(pages.value.tableSupplyChain, perPage.value.tableSupplyChain, search.value.supplyChain)
        supplyChainTable.value.totalItems = supplyChainStore.supplyChainTemplatesItemsCount
        supplyChainTable.value.data = supplyChainStore.supplyChainTemplates
    }
}

$listen('handleSupplyChainSubmitted', async () => {
    const page = getPage('tableSupplyChain') || 20
    const itemsPerPage = getTotalPerPage('tableSupplyChain') || 20

    await supplyChainStore.fetchSupplyChainTemplates(page, itemsPerPage).then(() => {
        supplyChainTable.value.totalItems = supplyChainStore.supplyChainTemplatesItemsCount
        supplyChainTable.value.data = supplyChainStore.supplyChainTemplates
        isLoading.value = false
    })
})

const handleRowClick = (rowData: any) => {
    navigateTo(`/dpps/supply-chain/${rowData.id}`)
}

const handleRowClickDpp = (rowData: any) => {
    if (rowData.type === 'Dpp') {
        navigateTo(`/dpps/detail/${rowData.id}?type=dpp`)
    }

    if (rowData.type === 'ProductStep') {
        navigateTo(`/dpps/detail/${rowData.id}?type=product-step`)
    }
}

const handleRowClickLogistics = (rowData: any) => {
    navigateTo(`/dpps/logistics/${rowData.id}`)
}

const handleOpenQrCodeModal = (qrId: string, qrImage: string, name?: string, typeDpp?: string) => {
    let type = typeDpp
    if (typeDpp === 'ProductStep') {
        type = 'STEP_DPP'
    }

    if (typeDpp === 'Dpp') {
        type = 'DPP'
    }

    $event('openQrCodeModal', { qrId, code: qrImage, name, typeDpp: type })
}

const handleOpenId = (id: number) => {
    $swal.fire({
        title: 'ID',
        text: id,
        confirmButtonColor: '#65c09e',
        cancelButtonColor: '#65c09e',
    })
}

const openEditSupplyChainModal = (supplyChain: SupplyChain) => {
    $event('editSupplyChain', supplyChain)
}

const handleEditDpp = (dpp: any) => {
    dppTable.value.data = Array.from(
        new Map(dppTable.value.data.map((item: any) => [item.id, item])).values(),
    )

    $event('openEditDppModal', dpp)
}

const dppTableKey = ref(0)

$listen('dppEdited', (success: boolean) => {
    if (success) {
        dppTableKey.value += 1
    }
})
</script>

<template>
    <NuxtLayout>
        <div class="w-100 d-flex flex-column">
            <VContainer fluid>
                <div class="product">
                    <TableData
                        key="supplyChainTable"
                        :search="search.supplyChain"
                        :items="supplyChainTable"
                        :is-delete="false"
                        :loading="isLoading"
                        :per-page="perPage.tableSupplyChain"
                        class="supply-chain-table"
                        @pagination="getPaginationDataSupplyChain"
                        @set-row-data="handleRowClick"
                    >
                        <template #table-title>
                            <div class="w-100 d-flex align-center justify-space-between">
                                <div class="header-title">
                                    <h3>
                                        {{ t('supplyChains.title') }}
                                    </h3>
                                </div>

                                <div class="d-flex">
                                    <div class="search-wrap">
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
                                    @click="handleRowClick(item)"
                                >
                                    <PhosphorIconEye
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>
                                <VBtn
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
                </div>
            </VContainer>

            <VContainer fluid>
                <div class="input-category">
                    <Legend
                        style="width: 100%"
                        class="my-8"
                        :data="legendData.dpps"
                        is-square
                    />
                    <TableData
                        :key="dppTableKey"
                        :search="search.dpp"
                        :items="dppTable"
                        :is-delete="false"
                        :loading="isLoading"
                        :per-page="perPage.tableDpp"
                        class="dpp-table"
                        @pagination="getPaginationDataDpp"
                        @set-row-data="handleRowClickDpp"
                    >
                        <template #table-title>
                            <div class="w-100 d-flex align-center justify-space-between">
                                <div class="header-title">
                                    <h3>
                                        {{ t('dpps.title') }}
                                    </h3>
                                </div>

                                <div class="d-flex">
                                    <div class="search-wrap">
                                        <VTextField
                                            v-model="search.dpp"
                                            :label="t('dpps.search')"
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
                            >
                                <VBtn
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.detail')"
                                    @click="handleRowClickDpp(item)"
                                >
                                    <PhosphorIconEye
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>
                                <VBtn
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.detailQRCode')"
                                    @click="handleOpenQrCodeModal(item.id, item.qrImage, item.name, item.type)"
                                >
                                    <PhosphorIconQrCode
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>

                                <VBtn
                                    v-if="!item.haveDpp"
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.detailId')"
                                    @click="handleOpenId(item.id)"
                                >
                                    <PhosphorIconHash
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>

                                <VBtn
                                    v-if="item.ongoingDpp || item.updatable || (item.createEmptyDpp || item.state === 'EMPTY_DPP')"
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.edit')"
                                    @click="handleEditDpp(item)"
                                >
                                    <PhosphorIconPencil
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>

                                <VBtn
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.downloadTsa')"
                                    @click="handleDownloadTsa(item.id, item.type)"
                                >
                                    <PhosphorIconFileArrowDown
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>
                            </div>
                        </template>

                        <template #table-tag="{ item }">
                            <div
                                :class="item.ongoingDpp
                                    ? 'ongoing-dpp'
                                    : item.createEmptyDpp ? 'create-empty-dpp'

                                        : {
                                            'not-assigned': item.state === 'NOT_ASSIGNED',
                                            'logistics': item.state === 'LOGISTICS',
                                            'in-use': item.state === 'IN_USE' || item.state === 'DPP_LOGISTICS',
                                            'export-dpp': item.state === 'EXPORT_DPP',
                                            'create-empty-dpp': item.state === 'EMPTY_DPP',
                                        }"
                            />
                        </template>
                    </TableData>
                </div>
            </VContainer>

            <VContainer fluid>
                <div class="input-category">
                    <Legend
                        style="width: 100%"
                        class="my-8"
                        :data="legendData.logistics"
                    />

                    <TableData
                        key="logisticsTable"
                        :search="search.logistics"
                        :items="logisticsTable"
                        :is-delete="false"
                        :loading="isLoading"
                        :per-page="perPage.tableLogistics"
                        class="logistics-table"
                        @pagination="getPaginationDataLogistics"
                        @set-row-data="handleRowClickLogistics"
                    >
                        <template #table-title>
                            <div class="w-100 d-flex align-center justify-space-between">
                                <div class="header-title">
                                    <h3>
                                        {{ t('logistics.logistics') }}
                                    </h3>
                                </div>

                                <div class="d-flex">
                                    <div class="search-wrap">
                                        <VTextField
                                            v-model="search.logistics"
                                            :label="t('logistics.search')"
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
                            >
                                <VBtn
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.detail')"
                                    @click="handleRowClickLogistics(item)"
                                >
                                    <PhosphorIconEye
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>

                                <VBtn
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.detailQRCode')"
                                    @click="handleOpenQrCodeModal(item.id, item.qrImage, item.name, 'LOGISTICS_DPP')"
                                >
                                    <PhosphorIconQrCode
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>

                                <VBtn
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.detailId')"
                                    @click="handleOpenId(item.id)"
                                >
                                    <PhosphorIconHash
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>

                                <VBtn
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.downloadTsa')"
                                    @click="handleDownloadTsa(item.id, 'Logistics')"
                                >
                                    <PhosphorIconFileArrowDown
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>
                            </div>
                        </template>

                        <template #table-tag="{ item }">
                            <div
                                :class="{
                                    'assigned-to-dpp': item.state === 'ASSIGNED_TO_DPP',
                                    'export-logistics': item.state === 'EXPORT_LOGISTICS',
                                    'in-use-logistics': item.state === 'IN_USE_LOGISTICS',
                                }"
                            />
                        </template>
                    </TableData>
                </div>
            </VContainer>
        </div>
    </NuxtLayout>
    <EditSupplyChain />
    <QrCodeModal />
    <EditDpp />
</template>

<style lang="scss">
.dpp-table,
.logistics-table {
    .v-table  {
        .v-table__wrapper {
            table {
                tbody {
                    tr {
                        td:first-child {
                            padding: 0;
                            .tag {
                                height: 100%;

                                .not-assigned,
                                .logistics,
                                .export-dpp,
                                .ongoing-dpp,
                                .export-logistics,
                                .in-use-logistics,
                                .in-use,
                                .assigned-to-dpp,
                                .create-empty-dpp {
                                    width: 1rem;
                                    height: 100%;
                                }

                                .not-assigned {
                                    border: 10px solid rgb(102, 255, 7);
                                }

                                .ongoing-dpp {
                                    border: 10px solid rgb(255, 165, 0);
                                }

                                .logistics,
                                .assigned-to-dpp {
                                    border: 10px solid rgb(52, 152, 219);
                                }

                                .in-use {
                                    border: 10px solid #FF007F;
                                }

                                .export-dpp,
                                .export-logistics {
                                    border: 10px solid rgb(255, 0, 0);
                                }

                                .in-use-logistics {
                                    border: 10px solid rgb(255, 0, 127, 1);
                                }

                                .create-empty-dpp {
                                    border: 10px solid #A020F0;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

.supply-chain-table {
    .v-table  {
        .v-table__wrapper {
            table {
                thead {
                    tr {
                        th {
                            padding-inline-start: 2rem;
                        }
                    }
                }

                tbody {
                    tr {
                        td {
                            padding-inline-start: 2rem;
                        }
                    }
                }
            }
        }
    }
}
</style>
