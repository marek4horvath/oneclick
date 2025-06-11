<script setup lang="ts">
import type { TableData } from '@/types/tableData'
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen } = useNuxtApp()
const { t } = useI18n()

const isDetailCategoryModalOpen = ref(false)
const categoryData = ref()

const inputTable = ref<TableData>({
    headers: [
        { key: 'name', title: t('inputTableHeader.name') },
        { key: 'type', title: t('inputTableHeader.type') },
    ],
    idTable: 'inputTable',
    totalItems: 0,
    data: [],
})

$listen('openDetailCategoryModal', category => {
    inputTable.value.data = category.productInputs
    categoryData.value = category
    isDetailCategoryModalOpen.value = true
})

const closeDetailCategoryModal = () => {
    isDetailCategoryModalOpen.value = false
}

const submitHandler = () => {
    // here you do whatever
}
</script>

<template>
    <ModalLayout
        :is-open="isDetailCategoryModalOpen"
        name="detail-category-modal"
        :title="`${$t('category.categoryTitle')}:${categoryData?.name}` || ''"
        button-cancel-text="Cancel"
        button-submit-text="Save"
        @modal-close="closeDetailCategoryModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="inputs-wrapper">
                <TableData
                    search=""
                    :items="inputTable"
                    :is-delete="false"
                    :loading="false"
                    hidden-footer
                    class="inputs-table"
                >
                    <template #table-input-Type="{ item }">
                        <div class="d-flex align-center">
                            <IconTypeInputs
                                class="me-2"
                                :type="item.type"
                            />
                            <span>{{ item.type }}</span>
                        </div>
                    </template>
                </TableData>
            </div>
        </template>

        <template #footer>
            <div />
        </template>
    </ModalLayout>
</template>

<style lang="scss">
.inputs-wrapper {
    max-height: 300px;
    overflow-y: auto;

    .inputs-table {
        margin: 0;
        width: 100%;

        .v-table {
            padding: 0px
        }
    }
}
</style>
