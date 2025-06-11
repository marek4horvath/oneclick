<script setup lang="ts">
import type { TableData } from '@/types/tableData'
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isDetailStepModalOpen = ref(false)

const step = ref()

const inputsTable = ref<TableData>({
    headers: [
        { title: t('inputTableHeader.name'), key: 'name' },
        { title: t('inputTableHeader.type'), key: 'inputType' },
    ],
    idTable: 'inputsTable',
    totalItems: 0,
    data: [],
})

$listen('openDetailStepModal', (pushedStep: any) => {
    isDetailStepModalOpen.value = true

    step.value = pushedStep.step
    inputsTable.value.data = pushedStep.step.inputs.map((input: any) => {
        return {
            name: input.name,
            inputType: input.type,
        }
    })
})

const closeDetailStepModal = () => {
    isDetailStepModalOpen.value = false
}

const submitHandler = () => {
    $event('openAddInputModal', step.value)
    isDetailStepModalOpen.value = false
}
</script>

<template>
    <ModalLayout
        :is-open="isDetailStepModalOpen"
        name="detail-step-modal"
        :title="`${$t('breadcrumbs.detail')} ${step?.name}`"
        button-submit-text="Save"
        class="add-process"
        @modal-close="closeDetailStepModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="step-wrapper">
                <TableData
                    :items="inputsTable"
                    :loading="false"
                    hidden-footer
                />
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                height="45"
                @click="submitHandler"
            >
                {{ $t('products.addInput') }}
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
