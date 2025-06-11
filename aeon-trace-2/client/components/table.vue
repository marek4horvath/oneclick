<script setup lang="ts">
import { PhosphorIconEye, PhosphorIconMagnifyingGlass } from '#components'

const props = defineProps<{
    tableTitle: string
    items: any[]
    headers: any[]
    enableSearch: boolean
    enablePagination: boolean
    enableAddButton: boolean
    actions: string[]
    pageCount: number
    page: number
}>()

const emits = defineEmits(['rowClick', 'pageChanged', 'action'])

const searchInput = ref('')
const localPage = ref(props.page)
const refreshKey = ref(0)

const filteredItems = computed(() => {
    if (!searchInput.value) {
        return props.items
    }

    return props.items.filter((item: any) =>
        Object.values(item).some((value: any) => value?.toString().toLowerCase().includes(searchInput.value.toLowerCase())),
    )
})

const { t } = useI18n()

watch(props.items, () => {
    refreshKey.value++
})

const handleRowClick = (_event: any, item: any) => {
    emits('rowClick', item.item)
}

const pageChanged = (page: number) => {
    emits('pageChanged', page)
}
</script>

<template>
    <VCard
        variant="flat"
        color="white"
        class="table-wrapper-card"
    >
        <VCardTitle class="d-flex justify-space-between">
            <span class="flex-grow-1">
                {{ t(props.tableTitle) }}
            </span>

            <VTextField
                v-if="props.enableSearch"
                v-model="searchInput"
                class="search-input"
                :label="t('table.search')"
                variant="outlined"
            >
                <template #append-inner>
                    <PhosphorIconMagnifyingGlass :size="24" />
                </template>
            </VTextField>

            <VBtn
                v-if="props.enableAddButton"
                color="#26A69A"
                variant="flat"
                class="ms-4"
                rounded="sm"
                style="height: 56px"
                @click="emits('action', 'add')"
            >
                {{ t('table.addButton') }}
            </VBtn>
        </VCardTitle>

        <VCardText>
            <VDataTable
                disable-sort
                :headers="props.headers"
                :items="filteredItems"
                @click:row="handleRowClick"
            >
                <template #header="header">
                    <tr>
                        <th
                            v-for="head in header"
                            :key="head.key"
                            :class="head.key === 'actions' ? 'text-right' : ''"
                        >
                            {{ t(head.text) }}
                        </th>
                    </tr>
                </template>

                <template #item="{ item }">
                    <tr>
                        <td
                            v-for="header in props.headers"
                            :key="header.key"
                            :class="header.key === 'actions' ? 'text-right' : ''"
                        >
                            <template v-if="header.key === 'actions'">
                                <slot
                                    name="actions"
                                    :item="item"
                                >
                                    <VBtn
                                        v-for="action in props.actions"
                                        :key="action"
                                        color="#26A69A"
                                        variant="plain"
                                        rounded="0"
                                        @click="emits('action', action, item)"
                                    >
                                        <PhosphorIconEye
                                            v-if="action === 'detail'"
                                            :size="24"
                                        />

                                        <PhosphorIconPencil
                                            v-if="action === 'edit'"
                                            :size="24"
                                        />

                                        <PhosphorIconTrash
                                            v-if="action === 'delete'"
                                            :size="24"
                                        />
                                    </VBtn>
                                </slot>
                            </template>
                            <template v-else>
                                {{ item[header.key] }}
                            </template>
                        </td>
                    </tr>
                </template>

                <template #bottom>
                    <div class="text-right pt-2">
                        <VPagination
                            v-if="props.enablePagination"
                            v-model="localPage"
                            :length="props.pageCount"
                            @update:model-value="pageChanged"
                        />
                    </div>
                </template>
            </VDataTable>
        </VCardText>
    </VCard>
</template>

<style lang="scss">
.v-table__wrapper {
    table {
        overflow: hidden;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }

    thead {
        .v-data-table__th {
            background-color: #26A69A;
            text-align: center !important;

            &:last-of-type {
                text-align: end !important;

                .v-data-table-header__content {
                    display: inline-block;
                    padding-right: 2rem;
                }
            }
        }
    }
}

.table-wrapper-card {
    padding: 1.5rem !important;

    .v-card-title {
        margin-bottom: 2rem;
        font-size: 30px;
        color: #26A69A;

        .search-input {
            max-width: 300px;
        }
    }
}

.v-pagination__list {
    justify-content: end !important;
}
</style>
