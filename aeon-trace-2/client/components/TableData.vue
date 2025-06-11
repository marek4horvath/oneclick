<script setup lang="ts">
const props = defineProps({
    search: {
        type: String,
        required: true,
    },

    items: {
        type: Object,
        required: true,
    },

    isDelete: {
        type: Boolean,
        default: false,
    },

    perPageOptions: {
        type: Array,
        default: () => [5, 10, 20, 100],
    },

    perPage: {
        type: Number,
        required: false,
    },

    loading: {
        type: Boolean,
        default: true,
    },

    hiddenFooter: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['setRowData', 'pagination', 'delete'])
const dialogDelete = ref(props.isDelete)
const tableData = ref(props.items)
const optionPerPage = ref(props.perPage)

const updatedItems = computed(() => ({
    headers: tableData.value.headers || [],
    totalItems: tableData.value.totalItems,
    idTable: tableData.value.idTable || '',
    data: Array.isArray(tableData.value.data) ? [...tableData.value.data] : [],
}))

const handleRowClick = (event: PointerEvent, rowData: any) => {
    emit('setRowData', rowData.item)
}

const handleUpdateOptions = (options: any) => {
    emit('pagination', options)
}

const closeDelete = () => {
    dialogDelete.value = false
}

watch(() => props.isDelete, newVal => {
    if (newVal) {
        dialogDelete.value = newVal
    } else {
        closeDelete()
    }
})
</script>

<template>
    <div class="table-wrapper">
        <VCard
            variant="flat"
            color="white"
        >
            <VCardTitle class="d-flex align-center px-5 mx-0">
                <slot name="table-title" />
            </VCardTitle>

            <VDivider />

            <VCardText class="px-5 mx-0">
                <VDataTableServer
                    :items-per-page="optionPerPage"
                    :search="search"
                    :filter-keys="['name']"
                    :headers="updatedItems.headers"
                    :items="updatedItems.data || []"
                    :items-per-page-options="perPageOptions"
                    :items-length="updatedItems.totalItems"
                    :items-per-page-text="$t('rowsPerPage')"
                    :item-value="updatedItems.idTable"
                    :loading="loading"
                    fixed-header
                    class="table"
                    :hide-default-footer="hiddenFooter"
                    @click:row="handleRowClick"
                    @update:options="handleUpdateOptions"
                >
                    <template #no-data>
                        <slot name="table-no-data" />
                    </template>

                    <template #item.name="{ item }">
                        <div v-if="!updatedItems.headers.some(header => header.key === 'email')">
                            <div v-if="item && item.email">
                                <strong>{{ item.name }}</strong>
                                <br>
                                <span>{{ item.email }}</span>
                            </div>

                            <div v-if="item && !item.email">
                                <span class="font-weight-bold">{{ item.name }}</span>
                            </div>
                        </div>

                        <div v-else>
                            <span class="font-weight-bold">{{ item.name }}</span>
                        </div>
                    </template>

                    <template #item.tag="{ item }">
                        <div
                            v-if="item"
                            class="tag"
                        >
                            <slot
                                name="table-tag"
                                :item="item"
                            />
                        </div>
                    </template>

                    <template #item.userData="{ item }">
                        <div v-if="item">
                            {{ item.userData?.firstName }} {{ item.userData?.lastName }}
                        </div>
                    </template>

                    <template #item.address="{ item }">
                        <div v-if="item && item.address">
                            {{ item.address.fullAddress }}
                            <br>
                            <span>{{ item.address.city }}</span>
                            <br>
                            <span>{{ item.address.country }}</span>
                        </div>
                    </template>

                    <template #item.actions="{ item }">
                        <div
                            v-if="item"
                            @click.stop
                        >
                            <slot
                                name="table-actions"
                                :item="item"
                            />
                        </div>
                    </template>

                    <template #item.type="{ item }">
                        <div
                            v-if="item"
                            @click.stop
                        >
                            <slot
                                name="table-input-Type"
                                :item="item"
                            />
                        </div>
                    </template>

                    <template #item.image="{ item }">
                        <div v-if="item && item.image">
                            <slot
                                name="table-image"
                                :item="item"
                            />
                        </div>
                        <div v-else>
                            <VImg
                                src="/assets/images/placeholder.png"
                                height="64"
                                width="64"
                                cover
                                class="circle-img"
                            />
                        </div>
                    </template>
                </VDataTableServer>
            </VCardText>
        </VCard>
    </div>
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
            background-color: rgb(var(--v-theme-primary));
            text-align: start !important;

            &:last-of-type {
                text-align: start !important;

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
        color: rgb(var(--v-theme-primary));

        .search-input {
            max-width: 300px;
        }
    }
}

.v-pagination__list {
    justify-content: end !important;
}
</style>
