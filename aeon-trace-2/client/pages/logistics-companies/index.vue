<script setup lang="ts">
import { PhosphorIconEye, PhosphorIconMagnifyingGlass, PhosphorIconPencil, PhosphorIconTrash } from '#components'
import type { TableData } from '@/types/tableData'
import { getPage, getTotalPerPage, getUrlQueryCountRows, setPage, setUrlQueryCountRows } from '@/helpers/urlQueryCountRows'
import { debounce } from '@/helpers/debounce'

definePageMeta({
    title: 'page.logistics.title',
    name: 'index-logistics',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { isAdmin, isCompanyManager } = useRoleAccess()
const authStore = useAuthStore()
const { $listen } = useNuxtApp()
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const isLoading = ref<boolean>(true)
const companiesStore = useCompaniesStore()
const logistics: any[] = ref()

const search = ref({
    logistics: '',
})

const currentCountRows = route.query.countRows
    ? JSON.parse(decodeURIComponent(route.query.countRows as string))
    : {}

const perPage = ref<{ [key: string]: number }>({
    logisticsTable: Object.keys(currentCountRows).length > 0 ? currentCountRows.logisticsTable : getTotalPerPage('logisticsTable') ?? 20,
})

const logisticsTable = ref<TableData>({
    headers: [
        { key: 'image', title: '', sortable: false },
        { key: 'name', title: t('logisticsTableHeader.company'), sortable: true },
        { key: 'typeOfTransportsNames', title: t('logisticsTableHeader.typeOfTransport'), sortable: false },
        { key: 'actions', title: t('logisticsTableHeader.actions'), sortable: false },
    ],
    idTable: 'logisticsTable',
    totalItems: 0,
    data: [],
})

const updateLogisticsTable = () => {
    logisticsTable.value.totalItems = companiesStore.getTotalItems
    logisticsTable.value.data = logistics.value.map((logistic: any) => {
        return {
            ...logistic,
            typeOfTransportsNames: logistic.typeOfTransportsNames || '----',
            image: logistic.companyLogo
                ? `${backendUrl.value}/media/company_logos/${logistic.companyLogo}`
                : null,
        }
    })
}

const fetchLogisticsCompanyData = async (page?: number, itemsPerPage?: number, searchName?: string, key?: string, order?: string) => {
    try {
        let logisticsCompany

        if (isAdmin()) {
            logisticsCompany = await companiesStore.fetchCompaniesListing(page, itemsPerPage, searchName, true, false, key, order)
        } else {
            logisticsCompany = await companiesStore.fetchCompaniesListing(page, itemsPerPage, searchName, true, false, key, order, authStore.getCompany)
        }

        logistics.value = logisticsCompany
    } finally {
        isLoading.value = false
    }
    updateLogisticsTable()
}

const debouncedFetchLogisticsCompaniesData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]

    fetchLogisticsCompanyData(pagination.page, pagination.itemsPerPage, pagination.search, sort?.key, sort?.order)
}, 500)

const getPaginationDataLogistics = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'logisticsTable', router, route)
    setPage('logisticsTable', pagination.page)
    logisticsTable.value.data = []
    perPage.value.logisticsTable = pagination.itemsPerPage
    debouncedFetchLogisticsCompaniesData(pagination)

    isLoading.value = false
}

const removeCompany = async (companyId: string) => {
    const deleteCompany = await companiesStore.deleteCompany(companyId)

    if (!deleteCompany) {
        return
    }

    const countRows = getUrlQueryCountRows('logisticsTable', route)

    await fetchLogisticsCompanyData(1, countRows)
}

const detailLogistics = (data: any) => {
    router.push(`/logistics-companies/detail/${data.id}`)
}

const openAddCompany = () => {
    router.push(`/logistics-companies/add-logistics-company/`)
}

const openTransportationTemplate = () => {
    router.push(`/logistics-companies/transportation-templates/`)
}

const openEditLogisticsCompanyModal = (company: any) => {
    router.push(`/logistics-companies/edit-logistics-company/${company.id}`)
}

const handleRowClick = (rowData: any) => {
    router.push(`/logistics-companies/detail/${rowData?.id}`)
}

$listen('saveNewLogisticsCompany', async () => {
    const page = getPage('logisticsTable') || 20
    const itemsPerPage = getTotalPerPage('logisticsTable') || 20

    await fetchLogisticsCompanyData(page, itemsPerPage)
})
</script>

<template>
    <NuxtLayout>
        <VContainer fluid>
            <div class="product">
                <TableData
                    key="logisticsTable"
                    :search="search.logistics"
                    :items="logisticsTable"
                    :is-delete="false"
                    :loading="isLoading"
                    :per-page="perPage.logisticsTable"
                    @pagination="getPaginationDataLogistics"
                    @set-row-data="handleRowClick"
                >
                    <template #table-title>
                        <div class="w-100 d-flex align-center justify-space-between">
                            <div class="header-title">
                                <h3>
                                    {{ t('logistics.logistics') }}
                                </h3>
                            </div>

                            <div class="d-flex mt-3">
                                <div class="search-wrap me-4">
                                    <VTextField
                                        v-model="search.logistics"
                                        :label="t('logistics.search')"
                                        hide-details
                                        class="search"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                    >
                                        <template #append-inner>
                                            <PhosphorIconMagnifyingGlass :size="24" />
                                        </template>
                                    </VTextField>
                                </div>

                                <div class="actions">
                                    <VBtn
                                        v-if="isAdmin()"
                                        class="me-2 text-uppercase"
                                        color="#26A69A"
                                        size="large"
                                        variant="flat"
                                        @click="openAddCompany"
                                    >
                                        {{ t('logistics.addLogisticsCompany') }}
                                    </VBtn>
                                    <VBtn
                                        class="me-2 text-uppercase"
                                        color="#26A69A"
                                        size="large"
                                        variant="flat"
                                        @click="openTransportationTemplate"
                                    >
                                        {{ t('logistics.transportationTemplates') }}
                                    </VBtn>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template #table-actions="{ item }">
                        <div
                            v-if="item"
                            class="actions"
                        >
                            <span :title="$t('actionsTablesTitle.detail')">
                                <PhosphorIconEye
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    :title="$t('actionsTablesTitle.detail')"
                                    @click="detailLogistics(item)"
                                />
                            </span>

                            <span :title="$t('actionsTablesTitle.edit')">
                                <PhosphorIconPencil
                                    v-if="isCompanyManager()"
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    @click="openEditLogisticsCompanyModal(item)"
                                />
                            </span>
                            <span :title="$t('actionsTablesTitle.delete')">
                                <PhosphorIconTrash
                                    v-if="isAdmin()"
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    @click="removeCompany(item.id)"
                                />
                            </span>
                        </div>
                    </template>

                    <template #table-image="{ item }">
                        <VImg
                            v-if="item"
                            :src="item.image"
                            height="64"
                            width="64"
                            contain
                            class="circle-img"
                        />
                    </template>
                </TableData>
            </div>
        </VContainer>
    </NuxtLayout>
</template>

<style>
.align-items-right {
    display: grid;
    align-items: end;
    justify-content: end;
}
</style>
