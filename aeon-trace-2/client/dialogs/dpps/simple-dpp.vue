<script lang="ts" setup>
import ModalLayout from '@/dialogs/modalLayout.vue'
import EditDpp from '~/dialogs/dpps/edit-dpp.vue'
import { debounce } from '@/helpers/debounce'

const { $listen, $event, $swal } = useNuxtApp()
const isSimpleDppModalOpen = ref(false)
const { t } = useI18n()
const dppStore = useDppStore()
const authStore = useAuthStore()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

// DPP Table
const dppTable = ref<TableData>({
    headers: [
        { key: 'tag', title: '', sortable: false },
        { key: 'name', title: t('dppTableHeader.name') },
        { key: 'numberOfInputs', title: t('dppTableHeader.numberOfInputs') },
        { key: 'createdAt', title: t('dppTableHeader.createdAt') },
        { key: 'tsaVerifiedAt', title: t('dppTableHeader.tsaVerified') },
        { key: 'userData', title: t('dppTableHeader.createdBy') },
        { key: 'actions', title: t('dppTableHeader.actions'), width: '201px', align: 'start' },
    ],
    idTable: 'dppTable',
    totalItems: 0,
    data: [],
})

const isLoading = ref<boolean>(true)
const typeDpp = ref<string>()
const node = ref()

const perPage = ref<{ [key: string]: number }>({
    tableDpp: 20,
})

const handleOpenQrCodeModal = (qrId: string, qrImage: string, name?: string, dppType?: string) => {
    let type = dppType

    if (dppType === 'ProductStep') {
        type = 'STEP_DPP'
    }

    if (dppType === 'Dpp') {
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

const handleRowClickDpp = (rowData: any) => {
    if (rowData.type === 'Dpp') {
        navigateTo(`/dpps/detail/${rowData.id}?type=dpp`)
    }

    if (rowData.type === 'ProductStep') {
        navigateTo(`/dpps/detail/${rowData.id}?type=product-step`)
    }
}

const closeSimpleDppModal = () => {
    isSimpleDppModalOpen.value = false
    typeDpp.value = ''
    isLoading.value = true
    dppTable.value.data = []
    dppTable.value.totalItems = 0
}

const fetchDpp = async (page: number = 1, itemsPerPage: number = 20, orderName?: string, orderValue?: string, searchName?: string) => {
    let onGoingDpp: any = false
    let emptyDpp: any = false
    let state: any

    if (typeDpp.value === 'ongoingDpp') {
        onGoingDpp = true
    } else if (typeDpp.value === 'emptyDpp') {
        emptyDpp = true
        state = 'EMPTY_DPP'
    } else {
        state = typeDpp.value
    }

    let dppData = await dppStore.fetchDppsByNode(node.value.id, state, onGoingDpp, emptyDpp, page, itemsPerPage, orderName, orderValue, searchName)

    if (state === 'IN_USE') {
        emptyDpp = true

        const additionalData = await dppStore.fetchDppsByNode(
            node.value.id,
            state,
            onGoingDpp,
            emptyDpp,
            page,
            itemsPerPage,
            orderName,
            orderValue,
            searchName,
        )

        dppData = dppData.concat(additionalData)
    }

    if (state === 'IN_USE') {
        onGoingDpp = true
        emptyDpp = false

        const additionalData = await dppStore.fetchDppsByNode(
            node.value.id,
            state,
            onGoingDpp,
            emptyDpp,
            page,
            itemsPerPage,
            orderName,
            orderValue,
            searchName,
        )

        dppData = dppData.concat(additionalData)
    }

    const uniqueDppsMap = new Map()

    dppData.forEach((dpp: any) => {
        uniqueDppsMap.set(dpp.id, {
            ...dpp,
            node: node.value,
        })
    })

    dppData = Array.from(uniqueDppsMap.values())

    dppTable.value.totalItems = dppStore.dppsItemsCount || 0
    dppTable.value.data = dppData || []
}

const debouncedFetchDppData = debounce((pagination: any) => {
    isLoading.value = true

    const sort = pagination?.sortBy?.[0] || { key: 'createdAt', order: 'desc' }

    fetchDpp(pagination.page, pagination.itemsPerPage, sort?.key, sort?.order, pagination.search)
    isLoading.value = false
}, 500)

const getPaginationDataDpp = (pagination: any) => {
    perPage.value.tableDpp = pagination.itemsPerPage
    debouncedFetchDppData(pagination)
}

$listen('openSimpleDppModal', async data => {
    isSimpleDppModalOpen.value = true
    typeDpp.value = data.type
    node.value = data.nodeData
})

const handleEditDpp = (dpp: any) => {
    $event('openEditDppModal', dpp)
}

const handleDownloadTsa = (id: string, type: string) => {
    const token = authStore.getAccessToken
    let url: string

    if (type === 'ProductStep') {
        url = `${backendUrl.value}/api/product-step-tsa-download/${id}`
    } else if (type === 'Dpp') {
        url = `${backendUrl.value}/api/dpp-tsa-download/${id}`
    }

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
        :is-open="isSimpleDppModalOpen"
        name="simple-dpp-modal"
        title="DPP"
        no-buttons
        width="70vw"
        @modal-close="closeSimpleDppModal"
    >
        <template #content>
            <VContainer class="w-100">
                <div class="input-category">
                    <TableData
                        key="dppTable"
                        :items="dppTable"
                        :is-delete="false"
                        :loading="isLoading"
                        :per-page="perPage.tableDpp"
                        class="dpp-table"
                        @pagination="getPaginationDataDpp"
                        @set-row-data="handleRowClickDpp"
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
                                    v-if="item.ongoingDpp || item.createEmptyDpp || item.state === 'EMPTY_DPP' || item.updatable"
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
                                    : item.createEmptyDpp || item.state === 'EMPTY_DPP' ? 'create-empty-dpp'

                                        : {
                                            'not-assigned': item.state === 'NOT_ASSIGNED',
                                            'logistics': item.state === 'LOGISTICS' || (item.state === 'IN_USE' && node.children?.length === 0),
                                            'in-use': (item.state === 'IN_USE' || item.state === 'DPP_LOGISTICS') && !(node.children?.length === 0),
                                            'export-dpp': item.state === 'EXPORT_DPP',
                                        }"
                            />
                        </template>
                    </TableData>
                </div>
            </VContainer>
        </template>
    </ModalLayout>
    <EditDpp />
</template>

<style lang="scss">
.dpp-table {
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
                                .in-use,
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

                                .logistics {
                                    border: 10px solid rgb(52, 152, 219);
                                }

                                .in-use {
                                    border: 10px solid #FF007F;
                                }

                                .export-dpp {
                                    border: 10px solid rgb(255, 0, 0);
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
</style>
