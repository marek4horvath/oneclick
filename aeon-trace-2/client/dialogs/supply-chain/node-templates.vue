<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'
import AddNodeTemplate from '@/dialogs/supply-chain/add-node-template.vue'
import type { TableData } from "~/types/tableData.ts"

const { $listen, $event } = useNuxtApp()

const productTemplatesStore = useProductTemplatesStore()
const { t } = useI18n()

const isNodeTemplatesModalOpen = ref(false)
const perPage = ref(5)

const fullData = ref<any[]>([])

const templatesTable = ref<TableData>({
    headers: [
        { key: 'name', title: t('stepTableHeader.name') },
        { key: 'actions', title: t('dppTableHeader.actions'), width: '200px', align: 'start' },
    ],
    idTable: 'templatesTable',
    totalItems: 0,
    data: [],
})

const payloadLoc = ref()

const updatePaginatedData = (page: number) => {
    const startIndex = (page - 1) * perPage.value
    const endIndex = startIndex + perPage.value

    templatesTable.value.data = fullData.value.slice(startIndex, endIndex)
}

const handleNodeTemplateAddSubmitted = (data: any) => {
    $event('handleNodeTemplateAddSubmitted', data)
}

$listen('handleNodeTemplateSubmitted', async productTemplate => {
    const response = await productTemplatesStore.getProductTemplate(productTemplate.id)
    const exists = templatesTable.value.data.some(template => template.id === response.id)

    if (!exists) {
        templatesTable.value.data.unshift(response)
        handleNodeTemplateAddSubmitted(response)
    }

    const currentPage = 1
    const startIndex = (currentPage - 1) * perPage.value
    const endIndex = startIndex + perPage.value

    templatesTable.value.data = templatesTable.value.data.slice(startIndex, endIndex)
})

$listen('openNodeTemplatesModal', async (payload: any) => {
    isNodeTemplatesModalOpen.value = true
    payloadLoc.value = payload
    fullData.value = []

    const fetchPromises = payload.supplyChain.nodeTemplates.map(async (template: any) => {
        return await productTemplatesStore.getProductTemplate(template.split('/').pop())
    })

    fullData.value = await Promise.all(fetchPromises)

    fullData.value.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))

    templatesTable.value.totalItems = fullData.value.length

    updatePaginatedData(1)
})

const openAddNodeTemplatesModal = () => {
    $event('openAddNodeTemplatesModal')
}

const openAddNodeTemplatesModalWithEdit = (template: any) => {
    $event('openAddNodeTemplatesModalWithEdit', template)
}

const closeNodeTemplatesModal = () => {
    isNodeTemplatesModalOpen.value = false
}

const deleteNodeTemplate = async (id: string) => {
    await productTemplatesStore.deleteProductTemplate(id)
}

const getPaginationDataNodeTemplate = (pagination: any) => {
    perPage.value = pagination.itemsPerPage

    const currentPage = pagination.page || 1

    const startIndex = (currentPage - 1) * perPage.value
    const endIndex = startIndex + perPage.value

    templatesTable.value.data = fullData.value.slice(startIndex, endIndex)
}
</script>

<template>
    <ModalLayout
        :is-open="isNodeTemplatesModalOpen"
        name="node-templates-modal"
        title="Node Templates"
        button-cancel-text="Cancel"
        button-submit-text="Add new node template"
        style="overflow-y: scroll;"
        class="node-template"
        @modal-close="closeNodeTemplatesModal"
        @submit="openAddNodeTemplatesModal"
    >
        <template #content>
            <TableData
                key="templatesTable"
                :items="templatesTable"
                :is-delete="false"
                :loading="false"
                :per-page="perPage"
                @pagination="getPaginationDataNodeTemplate"
                @set-row-data="openAddNodeTemplatesModalWithEdit"
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
                            @click="openAddNodeTemplatesModalWithEdit(item)"
                        >
                            <PhosphorIconPencilSimpleLine
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>

                        <VBtn
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            @click="deleteNodeTemplate(item.id)"
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
        </template>

        <template #footer>
            <VRow>
                <VCol
                    cols="12"
                    sm="3"
                >
                    <VBtn
                        variant="text"
                        class="submit-btn"
                        height="45"
                        @click="closeNodeTemplatesModal"
                    >
                        {{ t('page.product.detail.delete.cancelButtonText') }}
                    </VBtn>
                </VCol>

                <VCol class="d-none d-sm-flex" />

                <VCol
                    cols="12"
                    sm="6"
                >
                    <VBtn
                        variant="text"
                        class="submit-btn"
                        height="45"
                        @click="openAddNodeTemplatesModal"
                    >
                        {{ t('supplyChains.addNodeTemplate') }}
                    </VBtn>
                </VCol>
            </VRow>
        </template>
    </ModalLayout>

    <AddNodeTemplate />
</template>

<style scoped lang="scss">
.node-template.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        :global(.modal-footer) {
            margin-top: 2rem;
        }

        .modal-footer {
            .v-btn {
                padding-inline: 1rem;
                padding-block: 0.5rem;
                display: inline-block;
                border-radius: unset;
                flex: 1;
                transition: 0.5s all;

                &:hover {
                    background-color: rgba(167, 217, 212, 1) !important;
                    color: #000000 !important;
                }

                &.submit-btn {
                    background-color: rgba(38, 166, 154, 1);
                    color: #FFFFFF;
                    transition: 0.5s all;

                    &:hover {
                        background-color: rgba(167, 217, 212, 1);
                    }
                }
            }
        }
    }
}
</style>
