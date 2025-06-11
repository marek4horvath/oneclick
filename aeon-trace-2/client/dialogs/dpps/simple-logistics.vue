<script lang="ts" setup>
import ModalLayout from '@/dialogs/modalLayout.vue'
import { debounce } from '@/helpers/debounce'

const { $listen, $swal, $event } = useNuxtApp()
const isSimpleLogisticsModalOpen = ref(false)
const { t } = useI18n()
const authStore = useAuthStore()
const logisticsStore = useLogisticsStore()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

// Logistics Table
const logisticsTable = ref<TableData>({
    headers: [
        { key: 'tag', title: '', sortable: false },
        { key: 'name', title: t('logistics.logistics'), sortable: true },
        { key: 'totalInputs', title: t('products.numberOfInputs'), sortable: true },
        { key: 'createdAt', title: t('dpps.createdAt'), sortable: true },
        { key: 'tsaVerifiedAt', title: t('dppTableHeader.tsaVerified'), sortable: true },
        { key: 'userData', title: t('dppTableHeader.createdBy'), sortable: true },
        { key: 'actions', title: t('logisticsTableHeader.actions'), width: '201px', align: 'start', sortable: false },
    ],
    idTable: 'logisticsTable',
    totalItems: 0,
    data: [],
})

const isLoading = ref<boolean>(true)
const typeDpp = ref<string>()
const node = ref()

const perPage = ref<{ [key: string]: number }>({
    tableLogistics: 20,
})

const handleOpenQrCodeModal = (qrId: string, qrImage: string, name?: string, dppType?: string) => {
    $event('openQrCodeModal', { qrId, code: qrImage, name, typeDpp: dppType })
}

const handleOpenId = (id: number) => {
    $swal.fire({
        title: 'ID',
        text: id,
        confirmButtonColor: '#65c09e',
        cancelButtonColor: '#65c09e',
    })
}

const closeSimpleLogisticsModal = () => {
    isSimpleLogisticsModalOpen.value = false
    logisticsTable.value.data = []
    logisticsTable.value.totalItems = 0
    isLoading.value = true
}

const handleRowClickLogistics = (rowData: any) => {
    navigateTo(`/dpps/logistics/${rowData.id}`)
}

const fetchLogistice = async (page: number = 1, itemsPerPage: number = 20, orderName?: string, orderValue?: string, searchName?: string) => {
    logisticsTable.value.data = []
    let totalItems = 0
    const localData: any[] = []

    for (const parent of node.value.parents) {
        const logisticsData = await logisticsStore.fetchListingDpps(parent.id, node.value.id, typeDpp.value, page, itemsPerPage, orderName, orderValue, searchName)

        localData.push(...logisticsData)
        totalItems += logisticsStore.logisticsTotalItems
    }

    logisticsTable.value.data = localData
    logisticsTable.value.totalItems = totalItems || 0
    isLoading.value = false
}

const debouncedFetchLogisticsData = debounce((pagination: any) => {
    isLoading.value = true

    const sort = pagination?.sortBy?.[0] || { key: 'createdAt', order: 'desc' }

    fetchLogistice(pagination.page, pagination.itemsPerPage, sort?.key, sort?.order, pagination.search)
    isLoading.value = false
}, 500)

const getPaginationDataLogistics = (pagination: any) => {
    perPage.value.tableLogistics = pagination.itemsPerPage
    debouncedFetchLogisticsData(pagination)
}

$listen('openSimpleLogisticsModal', (data: any) => {
    isSimpleLogisticsModalOpen.value = true
    typeDpp.value = data.type
    node.value = data.nodeData
})

const handleDownloadTsa = (id: string) => {
    const token = authStore.getAccessToken
    const url = `${backendUrl.value}/api/logistics-tsa-download/${id}`

    const link = document.createElement('a')

    link.href = `${url}?token=${token}`
    link.download = ''
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}
</script>

<template>
    <ModalLayout
        :is-open="isSimpleLogisticsModalOpen"
        name="simple-logistics-modal"
        title="Logistics"
        no-buttons
        width="70vw"
        @modal-close="closeSimpleLogisticsModal"
    >
        <template #content>
            <VContainer class="w-100">
                <div class="input-category">
                    <TableData
                        key="logisticsTable"
                        :items="logisticsTable"
                        :is-delete="false"
                        :loading="isLoading"
                        class="logistics-table "
                        @pagination="getPaginationDataLogistics"
                        @set-row-data="handleRowClickLogistics"
                    >
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
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.downloadTsa')"
                                    @click="handleDownloadTsa(item.id)"
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
        </template>
    </ModalLayout>
</template>

<style lang="scss">
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

                                .export-logistics,
                                .assigned-to-dpp,
                                .in-use-logistics {
                                    width: 1rem;
                                    height: 100%;
                                }

                                .assigned-to-dpp {
                                    border: 10px solid rgb(52, 152, 219);
                                }

                                .export-logistics {
                                    border: 10px solid rgb(255, 0, 0);
                                }

                                .in-use-logistics {
                                    border: 10px solid rgb(255, 0, 127, 1);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
