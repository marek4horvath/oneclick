<script setup lang="ts">
import type { TableData } from '@/types/tableData'
import { PhosphorIconMagnifyingGlass, PhosphorIconPencil } from "#components"
import ModalLayout from '@/dialogs/modalLayout.vue'

// import AddNodeTemplate from '@/dialogs/process/add-node-template.vue'
// import EditNodeTemplate from '@/dialogs/process/edit-node-template.vue'

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

const fetchProcess = async () => {
    await processStore.fetchProcesses(undefined, undefined, 'step')

    processTable.value.data = processStore.getProcesses
}

$listen('openDetailProcessModal', async () => {
    isDetailProcessModalOpen.value = true
    fetchProcess()
})

$listen('handleProcessSubmitted', async () => {
    fetchProcess()
})

const closeDetailProcessModal = () => {
    isDetailProcessModalOpen.value = false
}

const openEditProcessModal = (process: any) => {
    $event('openEditProcessModal', process)
}

const submitHandler = async () => {
    $event('openAddProcessModal')
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
                        </div>
                    </template>
                </TableData>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                @click="submitHandler"
            >
                {{ $t('process.createNew') }}
            </VBtn>
        </template>
    </ModalLayout>
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
