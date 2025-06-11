<script setup lang="ts">
import type { TableData } from '@/types/tableData'
import { PhosphorIconMagnifyingGlass, PhosphorIconPencil } from "#components"
import ModalLayout from '@/dialogs/modalLayout.vue'
import AddProcess from '@/dialogs/process/add-process.vue'
import EditProcess from '@/dialogs/process/edit-process.vue'
import { debounce } from '@/helpers/debounce'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isDetailProcessModalOpen = ref(false)
const processStore = useProcessStore()

const processTable = ref<TableData>({
    headers: [
        { key: 'name', title: t('process.processName') },
        { key: 'color', title: t('process.color') },
        { key: 'actions', title: t('process.actions') },
    ],
    idTable: 'processTable',
    totalItems: 0,
    data: [],
})

const search = ref('')
const typeProcess_ = ref('step')

const fetchProcess = async (typeProcess: string = 'step', searchName?: string) => {
    await processStore.fetchProcesses(undefined, undefined, typeProcess, searchName)

    watchEffect(() => {
        let processData = processStore.getProcesses

        if (typeProcess === 'step') {
            processData = processData.map((proces: any) => {
                if (proces.steps?.length === 0) {
                    return {
                        ...proces,
                        isDelete: true,
                    }
                }

                return {
                    ...proces,
                    isDelete: false,
                }
            })
        }

        if (typeProcess === 'node') {
            processData = processData = processData.map((proces: any) => {
                if (proces.nodes?.length === 0) {
                    return {
                        ...proces,
                        isDelete: true,
                    }
                }

                return {
                    ...proces,
                    isDelete: false,
                }
            })
        }

        processTable.value.data = processData
    })
}

const debouncedFetchProcessData = debounce((pagination: any) => {
    fetchProcess(typeProcess_.value, pagination.search)
}, 500)

const getPaginationDataProcess = (pagination: any) => {
    debouncedFetchProcessData(pagination)
}

$listen('openDetailProcessModal', async (typeProcess: string = 'step') => {
    isDetailProcessModalOpen.value = true
    typeProcess_.value = typeProcess
    await fetchProcess(typeProcess)
})

$listen('handleProcessSubmitted', async () => {
    await fetchProcess()
})

const closeDetailProcessModal = () => {
    isDetailProcessModalOpen.value = false
}

const openEditProcessModal = (process: any) => {
    $event('openEditProcessModal', process)
}

const handleProcessDeleteSubmitted = () => {
    $event('handleProcessDeleteSubmitted')
}

const deleteProcess = async (process: any, processType: string = 'step') => {
    const processDelete = await processStore.deleteProcess(process.id, processType)

    if (processDelete === 500) {
        return
    }

    await fetchProcess(processType)
    handleProcessDeleteSubmitted()
    useNuxtApp().$event('message', {
        type: 'success',
        message: t('messages.deleteProcessSuccess'),
        title: 'Success',
    })
}

const submitHandler = async () => {
    $event('openAddProcessModal', typeProcess_.value)
}
</script>

<template>
    <ModalLayout
        :is-open="isDetailProcessModalOpen"
        name="detail-process-modal"
        :title="$t('process.process')"
        button-submit-text="Save"
        class="detail-process"
        @modal-close="closeDetailProcessModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="process-wrapper">
                <TableData
                    key="processTable"
                    :search="search"
                    :items="processTable"
                    :is-delete="false"
                    :loading="false"
                    hidden-footer
                    class="process-table"
                    @pagination="getPaginationDataProcess"
                >
                    <template #table-title>
                        <VTextField
                            v-model="search"
                            :label="t('process.search')"
                            variant="outlined"
                            density="compact"
                            type="text"
                            hide-details
                        >
                            <template #append-inner>
                                <PhosphorIconMagnifyingGlass :size="24" />
                            </template>
                        </VTextField>
                    </template>

                    <template #table-actions="{ item }">
                        <div
                            v-if="item"
                            class="actions"
                        >
                            <PhosphorIconPencil
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                                class="cursor-pointer me-2"
                                @click="openEditProcessModal(item)"
                            />

                            <PhosphorIconTrash
                                v-if="item.isDelete"
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                                class="cursor-pointer me-2"
                                @click="deleteProcess(item, item.processType)"
                            />
                        </div>
                    </template>
                </TableData>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                height="45"
                @click="submitHandler"
            >
                {{ $t('process.createNew') }}
            </VBtn>
        </template>
    </ModalLayout>

    <AddProcess />
    <EditProcess />
</template>

<style scoped lang="scss">
.detail-process.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        .modal-body {
            .form-wrapper {
                height: 400px;
                padding-top: 1rem;
                overflow-y: scroll;
            }
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

.process-wrapper {
    max-height: 300px;
    overflow-y: auto;

    .process-table {
        margin: 0;
        width: 100%;

        .v-table {
            padding: 0 !important;
        }

        .v-card-title {
            padding: 1rem 0 !important;
        }
    }
}
</style>
