<script setup lang="ts">
import { PhosphorIconMagnifyingGlass, PhosphorIconPencil, PhosphorIconTrash } from '#components'
import type { TableData } from '@/types/tableData'
import { getPage, getTotalPerPage, getUrlQueryCountRows, setPage, setUrlQueryCountRows } from '@/helpers/urlQueryCountRows'
import { debounce } from '@/helpers/debounce'
import LogisticsTemplateForm from '~/dialogs/logistics/logistics-template-form.vue'
import { useTransportationTemplateStore } from '~/stores/transportationTemplates.ts'

definePageMeta({
    title: 'logistics.logistics',
    name: 'transportation-templates',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const isLoading = ref<boolean>(true)
const transportationTemplatesStore = useTransportationTemplateStore()
const transportationTemplates: any[] = ref()
const countDefaultInput = 7

const search = ref({
    transportationTemplates: '',
})

const currentCountRows = route.query.countRows
    ? JSON.parse(decodeURIComponent(route.query.countRows as string))
    : {}

const perPage = ref<{ [key: string]: number }>({
    transportationTemplatesTable: Object.keys(currentCountRows).length > 0 ? currentCountRows.logisticsTable : getTotalPerPage('transportationTemplatesTable') ?? 20,
})

const transportationTemplatesTable = ref<TableData>({
    headers: [
        { key: 'name', title: t('transportationTemplateTableHeader.transportationTemplateName'), sortable: true },
        { key: 'numberOfInputs', title: t('transportationTemplateTableHeader.numberOfInputs'), sortable: true },
        { key: 'typeOfTransport', title: t('transportationTemplateTableHeader.typeOfTransport'), sortable: true },
        { key: 'actions', title: t('transportationTemplateTableHeader.actions'), sortable: false },
    ],
    idTable: 'transportationTemplatesTable',
    totalItems: 0,
    data: [],
})

const updateTransportationTemplatesTable = () => {
    transportationTemplatesTable.value.totalItems = transportationTemplatesStore.getTotalItems
    transportationTemplatesTable.value.data = transportationTemplates.value.map((transportationTemplate: any) => {
        return {
            ...transportationTemplate,
            name: transportationTemplate.name,
            numberOfInputs: transportationTemplate.inputDetails.length + countDefaultInput,
            typeOfTransport: transportationTemplate.typeOfTransport?.name,
        }
    })
}

const fetchTransportationTemplatesData = async (page?: number, itemsPerPage?: number, searchName?: string, key?: string, order?: string) => {
    try {
        await transportationTemplatesStore.fetchTransportationTemplates(page, itemsPerPage, searchName, key, order)
    } finally {
        isLoading.value = false
    }
    transportationTemplates.value = transportationTemplatesStore.getTransportationTemplates
    updateTransportationTemplatesTable()
}

const debouncedFetchLogisticsCompaniesData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]

    fetchTransportationTemplatesData(pagination.page, pagination.itemsPerPage, pagination.search, sort?.key, sort?.order)
}, 500)

const getPaginationDataLogistics = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'transportationTemplate', router, route)
    setPage('transportationTemplate', pagination.page)
    transportationTemplatesTable.value.data = []
    perPage.value.logisticsTable = pagination.itemsPerPage
    debouncedFetchLogisticsCompaniesData(pagination)

    isLoading.value = false
}

const removeTransportationTemplates = async (transportationTemplateId: string) => {
    const deleteTransportationTemplate = await transportationTemplatesStore.deleteTransportationTemplate(transportationTemplateId)

    if (!deleteTransportationTemplate) {
        return
    }

    const countRows = getUrlQueryCountRows('transportationTemplate', route)

    await fetchTransportationTemplatesData(1, countRows)
}

const openLogisticsTemplateForm = (logisticsTemplate?: any) => {
    $event('openLogisticsTemplateForm', logisticsTemplate)
}

const handleRowClick = (logisticsTemplate: any) => {
    $event('openLogisticsTemplateForm', logisticsTemplate)
}

$listen('saveLogisticsTemplate', async () => {
    const page = getPage('transportationTemplate') || 20
    const itemsPerPage = getTotalPerPage('transportationTemplate') || 20

    await fetchTransportationTemplatesData(page, itemsPerPage)
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer fluid>
            <div class="product">
                <TableData
                    key="transportationTemplatesTable"
                    :search="search.transportationTemplates"
                    :items="transportationTemplatesTable"
                    :is-delete="false"
                    :loading="isLoading"
                    :per-page="perPage.transportationTemplatesTable"
                    @pagination="getPaginationDataLogistics"
                    @set-row-data="handleRowClick"
                >
                    <template #table-title>
                        <VRow
                            class="align-center pa-3"
                            style="margin-bottom: -30px"
                        >
                            <VCol
                                cols="6"
                                class="header-title"
                            >
                                <h3>
                                    {{ t('transportationTemplates.logisticsTemplates') }}
                                </h3>
                            </VCol>

                            <VRow>
                                <VCol class="mt-3">
                                    <VTextField
                                        v-model="search.transportationTemplates"
                                        :label="t('transportationTemplates.search')"
                                        hide-details
                                        class="search"
                                        variant="outlined"
                                        type="text"
                                    >
                                        <template #append-inner>
                                            <PhosphorIconMagnifyingGlass :size="24" />
                                        </template>
                                    </VTextField>
                                </VCol>

                                <VCol class="flex-grow-0 mt-6 me-2">
                                    <VRow class="align-center">
                                        <div class="actions">
                                            <VBtn
                                                class="me-2 text-uppercase"
                                                color="#26A69A"
                                                size="large"
                                                @click="openLogisticsTemplateForm"
                                            >
                                                {{ t('transportationTemplates.addTransportationTemplate') }}
                                            </VBtn>
                                        </div>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VRow>
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
                                @click="openLogisticsTemplateForm(item)"
                            />

                            <PhosphorIconTrash
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                                class="cursor-pointer me-2"
                                @click="removeTransportationTemplates(item.id)"
                            />
                        </div>
                    </template>
                </TableData>
            </div>
        </VContainer>
        <LogisticsTemplateForm />
    </NuxtLayout>
</template>
