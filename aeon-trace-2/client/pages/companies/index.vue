<script setup lang="ts">
import { PhosphorIconEye, PhosphorIconPencilSimple, PhosphorIconTrash } from '#components'
import type { TableData } from '@/types/tableData'
import type { CompaniesState } from '@/types/companiesStore'
import { getTotalPerPage, getUrlQueryCountRows, setUrlQueryCountRows } from '@/helpers/urlQueryCountRows'
import { debounce } from '@/helpers/debounce'
import InviteCompany from '~/dialogs/companies/invite-company.vue'

definePageMeta({
    title: 'page.companies.title',
    name: 'index-companies',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { isAdmin, isCompanyManager } = useRoleAccess()
const authStore = useAuthStore()
const { $event } = useNuxtApp()
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const isLoading = ref<boolean>(true)
const companiesStore = useCompaniesStore()
const companies: CompaniesState[] = ref()

const currentCountRows = route.query.countRows
    ? JSON.parse(decodeURIComponent(route.query.countRows as string))
    : {}

const perPage = ref<{ [key: string]: number }>({
    tableCompany: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableCompany : getTotalPerPage('tableCompany') ?? 20,
})

const companyTable = ref<TableData>({
    headers: [
        { key: 'image', title: '', width: '40px' },
        { key: 'name', title: t('companyTableHeader.companyName'), sortable: true },
        { key: 'totalProductTemplate', title: t('companyTableHeader.numberOfProduct'), sortable: true },
        { key: 'totalDpps', title: t('companyTableHeader.numberOfDpp'), sortable: true },
        { key: 'totalUsers', title: t('companyTableHeader.numberOfUser'), sortable: true },
        { key: 'actions', title: t('companyTableHeader.actions'), sortable: false },

    ],
    idTable: 'tableCompany',
    totalItems: 0,
    data: [],
})

const search = ref({
    company: '',
})

const fetchCompanyData = async (page?: number, itemsPerPage?: number, searchName?: string, key?: string, order?: string) => {
    try {
        if (isAdmin()) {
            await companiesStore.fetchCompaniesListing(page, itemsPerPage, searchName, false, true, key, order)
        } else {
            await companiesStore.fetchCompaniesListing(page, itemsPerPage, searchName, false, true, key, order, authStore.getCompany)
        }

        companyTable.value.totalItems = companiesStore.totalItems
        companies.value = companiesStore.companies

        companyTable.value.data = companies.value.map((company: any) => ({
            ...company,
            image: company.companyLogo
                ? `${backendUrl.value}/media/company_logos/${company.companyLogo}`
                : null,
        }))
    } finally {
        isLoading.value = false
    }
}

const debouncedFetchCompanytData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]

    fetchCompanyData(pagination.page, pagination.itemsPerPage, pagination.search, sort?.key, sort?.order)
}, 500)

const getPaginationDataCompany = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableCompany', router, route)
    companyTable.value.data = []
    perPage.value.tableProduct = pagination.itemsPerPage
    debouncedFetchCompanytData(pagination)

    isLoading.value = false
}

const removeCompany = async (companyId: string) => {
    const deleteCompany = await companiesStore.deleteCompany(companyId)

    if (!deleteCompany) {
        return
    }

    const countRows = getUrlQueryCountRows('tableCompany', route)

    await fetchCompanyData(1, countRows)
}

const handleRowClick = (rowData: any) => {
    router.push(`/companies/detail/${rowData.id}`)
}

const openInviteCompanyModal = () => {
    $event('openInviteCompanyModal')
}

onMounted(() => {
    isLoading.value = true
})
</script>

<template>
    <NuxtLayout>
        <VContainer fluid>
            <div class="companies">
                <TableData
                    :search="search.company"
                    :items="companyTable"
                    :per-page="perPage.tableCompany"
                    :loading="isLoading"
                    @pagination="getPaginationDataCompany"
                    @set-row-data="handleRowClick"
                >
                    <template #table-no-data>
                        <div class="text-center">
                            <template v-if="isLoading">
                                {{ t('tableLoadingData.loadingCompanies') }}
                            </template>
                            <template v-else>
                                {{ t('tableEmptyData.emptyCompanies') }}
                            </template>
                        </div>
                    </template>

                    <template #table-title>
                        <div class="w-100 d-flex align-center justify-space-between">
                            <div class="header-title">
                                <h3>
                                    {{ t('companies.companies') }}
                                </h3>
                            </div>

                            <div class="d-flex mt-3">
                                <div class="search-wrap me-4">
                                    <VTextField
                                        v-model="search.company"
                                        :label="t('companies.search')"
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

                                <div
                                    v-if="isAdmin()"
                                    class="actions"
                                >
                                    <RouterLink to="/companies/add-company?logistics=false">
                                        <VBtn
                                            class="me-2 text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            height="55"
                                        >
                                            {{ t('companies.addCompany') }}
                                        </VBtn>
                                    </RouterLink>
                                    <VBtn
                                        class="me-2 text-uppercase"
                                        color="#26A69A"
                                        size="large"
                                        height="55"
                                        @click="openInviteCompanyModal"
                                    >
                                        {{ t('companies.inviteCompany') }}
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
                            <RouterLink
                                :to="`/companies/detail/${item.id}`"
                                :title="$t('actionsTablesTitle.detail')"
                            >
                                <PhosphorIconEye
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                />
                            </RouterLink>

                            <RouterLink
                                v-if="isCompanyManager()"
                                :to="`/companies/edit-company/${item.id}`"
                                :title="$t('actionsTablesTitle.edit')"
                            >
                                <PhosphorIconPencilSimple
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                />
                            </RouterLink>
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
                            lazy-src="/assets/images/placeholder.png"
                        />
                    </template>
                </TableData>
            </div>
        </VContainer>
        <InviteCompany />
    </NuxtLayout>
</template>
