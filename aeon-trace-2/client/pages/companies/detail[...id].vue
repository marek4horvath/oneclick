<script setup lang="ts">
import { PhosphorIconEye, PhosphorIconPencilSimple, PhosphorIconTrash } from '#components'
import type { TableData } from '@/types/tableData'
import { getTotalPerPage, setUrlQueryCountRows } from '@/helpers/urlQueryCountRows'
import EditProduct from '~/dialogs/products/edit-product.vue'
import AddProduct from '~/dialogs/products/add-product.vue'
import LinkProduct from '~/dialogs/products/link-product.vue'
import AddUser from '~/dialogs/users/add-user.vue'
import EditUser from '~/dialogs/users/edit-user.vue'
import { debounce } from "~/helpers/debounce.ts"

definePageMeta({
    title: 'page.companies.detail.title',
    name: 'detail-companies-id',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { isCompanyManager } = useRoleAccess()
const { $listen } = useNuxtApp()
const { $event } = useNuxtApp()
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

const idParam = route.params.id

const companyId = Array.isArray(idParam)
    ? idParam.find(part => /^[\w-]{36}$/.test(part)) // Looks for UUID in field
    : idParam

const company = ref()
const isLoading = ref(true)

const companiesStore = useCompaniesStore()
const productsStore = useProductsStore()
const sitesStore = useSitesStore()
const siteImagesStore = useSiteImagesStore()
const usersStore = useUsersStore()

const coordinatesSite = ref({
    lat: 0,
    lng: 0,
})

const coordinates = ref({
    state: '',
    city: '',
    street: '',
    zip: '',
    houseNo: '',
    lat: 0,
    lng: 0,
})

const search = ref({
    product: '',
    site: '',
    user: '',
})

const currentCountRows = route.query.countRows
    ? JSON.parse(decodeURIComponent(route.query.countRows as string))
    : {}

const perPage = ref<{ [key: string]: number }>({
    tableProduct: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableProduct : getTotalPerPage('tableProduct') ?? 20,
    tableSite: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableSite : getTotalPerPage('tableSite') ?? 20,
    tableUser: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableUser : getTotalPerPage('tableUser') ?? 20,
})

const productTable = ref<TableData>({
    headers: [
        { key: 'image', title: '', sortable: false },
        { key: 'name', title: t('productTableHeader.productName'), sortable: true },
        { key: 'companiesNames', title: t('productTableHeader.company'), sortable: false, width: '401px' },
        { key: 'totalSteps', title: t('productTableHeader.numberOfStep'), sortable: true },
        { key: 'totalInputs', title: t('productTableHeader.numberOfInput'), sortable: true },
        { key: 'actions', title: t('productTableHeader.actions'), sortable: false },
    ],
    idTable: 'productTable',
    totalItems: 0,
    data: [],
})

const siteTable = ref<TableData>({
    headers: [
        { key: 'image', title: '', sortable: false },
        { key: 'name', title: t('siteTableHeader.name'), sortable: true },
        { key: 'address', title: t('siteTableHeader.address'), sortable: false },
        { key: 'actions', title: t('siteTableHeader.actions'), sortable: false },
    ],
    idTable: 'productTable',
    totalItems: 0,
    data: [],
})

const userTable = ref<TableData>({
    headers: [
        { key: 'image', title: '', sortable: false },
        { key: 'name', title: t('userTableHeader.name'), sortable: true },
        { key: 'email', title: t('userTableHeader.email'), sortable: true },
        { key: 'lastLogOn', title: t('userTableHeader.lastLogOn'), sortable: false }, // Temporary set to false - till backend is updated to hold last login info.
        { key: 'actions', title: t('userTableHeader.actions'), sortable: false },
    ],
    idTable: 'productTable',
    totalItems: 0,
    data: [],
})

const searchData = ref({
    products: [],
    sites: [],
    users: [],
})

const updatedSite = async (data: any) => {
    return await Promise.all(
        data?.map(async (siteItem: any) => {
            const updatedSiteItem = {
                ...siteItem,
                address: {
                    ...siteItem.address,
                    fullAddress: `${siteItem.address.street} ${siteItem.address.houseNo}`,
                },
            }

            if (siteItem.siteImages?.[0]?.image) {
                updatedSiteItem.image = `${backendUrl.value}/media/company_sites_images/${siteItem.siteImages[0].image}`
            } else if (siteItem.siteImages?.length && siteItem.siteImages[0]) {
                const siteImagesId = siteItem.siteImages[0].split('/').pop()
                const getCompanySitesImagesResponse = await siteImagesStore.fetchSiteImageById(siteImagesId)
                const image = getCompanySitesImagesResponse?.image

                updatedSiteItem.image = image ? `${backendUrl.value}/media/company_sites_images/${image}` : null
            }

            return updatedSiteItem
        }),
    )
}

const updatedUser = (data: any) => {
    return data.map((userItem: any) => {
        const fullName = `${userItem.firstName} ${userItem.lastName}`

        return {
            ...userItem,
            name: fullName,
        }
    })
}

const fetchCompanyData = async () => {
    await companiesStore.fetchCompanyById(companyId)
    company.value = companiesStore.getCompanyById(companyId)

    if (company.value.companyLogo !== '') {
        company.value.companyLogo = `${backendUrl.value}/media/company_logos/${company.value.companyLogo}`
    }

    if (company.value?.address) {
        company.value.fullAddress = `${company.value?.address.street} ${company.value?.address.houseNo}, ${company.value?.address.postcode}, ${company.value?.address.city}, ${company.value?.address.country}`
    }

    coordinates.value = {
        state: company.value.address.country,
        city: company.value.address.city,
        street: company.value.address.street,
        zip: company.value.address.postcode,
        houseNo: company.value.address.houseNo,
        lat: company.value.latitude,
        lng: company.value.longitude,
    }

    productTable.value.totalItems = productsStore.totalItems

    isLoading.value = false
}

const searchInData = (data: any, searchPhrase: string, fields: string[] = ['name']) => {
    if (!searchPhrase || searchPhrase.trim() === "") {
        return [...data]
    }

    const lowerSearchPhrase = searchPhrase.toLowerCase()

    return data.filter((item: any) => {
        return fields.some(field => {
            const fieldParts = field.split('.')
            let value = item

            for (const part of fieldParts) {
                if (value && Object.prototype.hasOwnProperty.call(value, part)) {
                    value = value[part]
                } else {
                    value = undefined
                    break
                }
            }

            return value && value.toString().toLowerCase().includes(lowerSearchPhrase)
        })
    })
}

const fetchCompanyProductsData = async (page?: number, itemsPerPage?: number, searchName?: string, key?: string, order?: string) => {
    try {
        const data = await productsStore.fetchProductsListing(page, itemsPerPage, searchName, key, order, companyId)

        productTable.value.totalItems = productsStore.totalItems
        productTable.value.data = data.map((product: any) => {
            return {
                ...product,
                image: product?.productImage ? `${backendUrl.value}/media/product_images/${product.productImage}` : '',
            }
        })
        company.value.productTemplates = data
    } finally {
        isLoading.value = false
    }
}

const debouncedFetchCompanyProductsData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]
    let sortKey

    if (sort?.key === 'totalSteps') {
        sortKey = 'steps'
    }

    if (sort?.key === 'totalInputs') {
        sortKey = 'inputs'
    }

    fetchCompanyProductsData(pagination.page, pagination.itemsPerPage, pagination.search, sortKey, sort?.order)
}, 500)

const getPaginationDataProduct = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableProduct', router, route)
    perPage.value.tableProduct = pagination.itemsPerPage
    debouncedFetchCompanyProductsData(pagination)
    productTable.value.data = searchInData(searchData.value.products, pagination.search)

    isLoading.value = false
}

const fetchCompanySitesData = async (page?: number, itemsPerPage?: number, searchName?: string, key?: string, order?: string) => {
    try {
        await sitesStore.fetchSites(page, itemsPerPage, searchName, companyId, key, order)

        const updatedSiteData = await updatedSite(sitesStore.sites)

        searchData.value.sites = updatedSiteData
        siteTable.value.data = updatedSiteData
        siteTable.value.totalItems = updatedSiteData.length
        coordinatesSite.value = updatedSiteData.map((site: any) => {
            return {
                lat: site.latitude,
                lng: site.longitude,
            }
        })
    } finally {
        isLoading.value = false
    }
}

const debouncedFetchCompanySitesData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]

    fetchCompanySitesData(pagination.page, pagination.itemsPerPage, pagination.search, sort?.key, sort?.order)
}, 500)

const getPaginationDataSite = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableSite', router, route)
    debouncedFetchCompanySitesData(pagination)

    siteTable.value.data = searchInData(searchData.value.sites, pagination.search,
        [
            'name',
            'address.city',
            'address.country',
            'address.houseNo',
            'address.postcode',
            'address.street',
        ],
    )
    perPage.value.tableSite = pagination.itemsPerPage

    isLoading.value = false
}

const fetchCompanyUsersData = async (page?: number, itemsPerPage?: number, searchName?: string, key?: string, order?: string) => {
    try {
        await usersStore.fetchUsers(page, itemsPerPage, searchName, companyId, key, order)

        const updatedUserData = await updatedUser(usersStore.users)

        searchData.value.users = updatedUserData
        userTable.value.data = updatedUserData
        userTable.value.totalItems = updatedUserData.length
    } finally {
        isLoading.value = false
    }
}

const debouncedFetchCompanyUsersData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]

    fetchCompanyUsersData(pagination.page, pagination.itemsPerPage, pagination.search, sort?.key, sort?.order)
}, 500)

const getPaginationDataUser = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableUser', router, route)
    debouncedFetchCompanyUsersData(pagination)

    userTable.value.data = searchInData(searchData.value.users, pagination.search,
        ['name', 'email', 'firstName', 'lastName'],
    )
    perPage.value.tableUser = pagination.itemsPerPage

    isLoading.value = false
}

const removeProduct = async (productId: string) => {
    const deleteProduct = await productsStore.deleteProduct(productId)

    if (!deleteProduct) {
        return
    }

    fetchCompanyProductsData(undefined, getTotalPerPage('tableProduct') || 20)
}

const removeSite = async (siteId: string) => {
    const deleteSite = await sitesStore.deleteSite(siteId)

    if (!deleteSite) {
        return
    }

    fetchCompanySitesData(undefined, getTotalPerPage('tableSite') || 20)
}

const removeUser = async (userId: string) => {
    const deleteUserResponse = await usersStore.deleteUser(userId)

    if (!deleteUserResponse) {
        return
    }

    fetchCompanyUsersData(undefined, getTotalPerPage('tableUser') || 20)
}

const detailProduct = (data: any) => {
    router.push(`/products/detail/${data.id}`)
}

const handleRowClickProduct = (rowData: any) => {
    if (rowData?.idCompany) {
        router.push(`/products/detail/${rowData?.idCompany}/${rowData.id}`)
    } else {
        router.push(`/products/detail/${rowData.id}`)
    }
}

const handleRowClickSite = (rowData: any) => {
    if (isCompanyManager()) {
        router.push(`/sites/edit-sites/${rowData.id}`)
    }
}

const openAddProductModal = () => {
    $event('openAddProductModal', companyId)
}

const openEditProductModal = (product: any) => {
    $event('openEditProductModal', product)
}

const openLinkProductModal = () => {
    $event('openLinkProductModal', company.value.productTemplates)
}

const openAddUserModal = () => {
    $event('openAddUserModal', companyId)
}

const openEditUserModal = (user: any) => {
    $event('openEditUserModal', user)
}

$listen('handleProductSubmitted', async () => {
    await fetchCompanyProductsData()
})

$listen('handleUserSubmitted', async () => {
    await fetchCompanyData()
    await fetchCompanyUsersData(undefined, getTotalPerPage('tableUser') ?? 20)
})

$listen('handleLinkProductSubmitted', async () => {
    await fetchCompanyProductsData()
})

onMounted(async () => {
    await fetchCompanyData()
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer fluid>
            <div
                v-if="!isLoading"
                class="detail-company"
            >
                <header>
                    <VRow>
                        <VCol
                            cols="12"
                            md="3"
                        >
                            <VImg
                                :src="company.companyLogo"
                                height="141"
                                width="212"
                                class="ma-auto"
                                lazy-src="/assets/images/placeholder.png"
                            />
                        </VCol>

                        <VCol
                            cols="12"
                            md="9"
                        >
                            <VRow class="mt-1">
                                <h2> {{ company.name }}</h2>
                            </VRow>

                            <VRow>
                                <p class="address">
                                    {{ company.fullAddress }}
                                </p>
                            </VRow>

                            <VRow>
                                <div class="description">
                                    <p>{{ company.description }}</p>
                                </div>
                            </VRow>
                        </VCol>
                    </VRow>
                </header>

                <VCard
                    class="map"
                    variant="flat"
                >
                    <Map
                        :is-active-map="false"
                        :is-marker-clicked="false"
                        :address-groups="[
                            {
                                addresses: [{ lat: coordinates.lat, lng: coordinates.lng }],
                                color: 'blue',
                                connectLine: false,
                            },

                            {
                                addresses: coordinatesSite,
                                color: 'green',
                                connectLine: false,
                            },

                        ]"
                    />
                </VCard>

                <div
                    v-if="(company.logisticsCompany && company.productCompany) || !company.logisticsCompany"
                    class="product"
                >
                    <TableData
                        key="productTable"
                        :search="search.product"
                        :items="productTable"
                        :loading="isLoading"
                        :per-page="perPage.tableProduct"
                        @pagination="getPaginationDataProduct"
                        @set-row-data="handleRowClickProduct"
                    >
                        <template #table-no-data>
                            <div class="textt-center">
                                {{ $t('tableEmptyData.emptyProducts') }}
                            </div>
                        </template>

                        <template #table-title>
                            <div class="mx-5 w-100 d-flex align-center justify-space-between">
                                <div class="header-title">
                                    <h3>
                                        {{ t('products.products') }}
                                    </h3>
                                </div>

                                <div class="d-flex">
                                    <div class="search-wrap me-4">
                                        <VTextField
                                            v-model="search.product"
                                            :label="t('products.search')"
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
                                            v-if="isCompanyManager()"
                                            class="text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openAddProductModal"
                                        >
                                            {{ t('products.addProduct') }}
                                        </VBtn>

                                        <VBtn
                                            v-if="isCompanyManager()"
                                            class="text-uppercase mx-3"
                                            color="#26A69A"
                                            size="large"
                                            @click="openLinkProductModal"
                                        >
                                            {{ t('products.linkProduct') }}
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
                                <PhosphorIconEye
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    :title="$t('actionsTablesTitle.detail')"
                                    @click="detailProduct(item)"
                                />

                                <PhosphorIconPencilSimple
                                    v-if="isCompanyManager()"
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    :title="$t('actionsTablesTitle.edit')"
                                    @click="openEditProductModal(item)"
                                />

                                <PhosphorIconTrash
                                    v-if="!item.haveDpp && isCompanyManager()"
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    :title="$t('actionsTablesTitle.delete')"
                                    @click="removeProduct(item.id)"
                                />
                            </div>
                        </template>

                        <template #table-image="{ item }">
                            <VImg
                                v-if="item"
                                :src="item.image"
                                height="64"
                                width="64"
                                cover
                                class="circle-img"
                                lazy-src="/assets/images/placeholder.png"
                            />
                        </template>
                    </TableData>
                </div>

                <div class="site">
                    <TableData
                        key="siteTable"
                        :search="search.site"
                        :items="siteTable"
                        :loading="isLoading"
                        :per-page="perPage.tableSite"
                        @pagination="getPaginationDataSite"
                        @set-row-data="handleRowClickSite"
                    >
                        <template #table-no-data>
                            <div class="textt-center">
                                {{ t('tableEmptyData.emptySite') }}
                            </div>
                        </template>

                        <template #table-title>
                            <div class="mx-5 w-100 d-flex align-center justify-space-between">
                                <div class="header-title">
                                    <h3>
                                        {{ t('sites.sites') }}
                                    </h3>
                                </div>

                                <div class="d-flex">
                                    <div class="search-wrap me-4">
                                        <VTextField
                                            v-model="search.site"
                                            :label="t('sites.search')"
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
                                        <RouterLink
                                            v-if="isCompanyManager()"
                                            :to="`/sites/add-sites/${companyId}`"
                                        >
                                            <VBtn
                                                class="text-uppercase"
                                                color="#26A69A"
                                                size="large"
                                            >
                                                {{ t('sites.addSite') }}
                                            </VBtn>
                                        </RouterLink>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template #table-actions="{ item }">
                            <div
                                v-if="item"
                                class="actions"
                            >
                                <RouterLink :to="`/sites/edit-sites/${item.id}`">
                                    <PhosphorIconPencilSimple
                                        v-if="isCompanyManager()"
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                        class="cursor-pointer me-2"
                                        :title="$t('actionsTablesTitle.edit')"
                                    />
                                </RouterLink>

                                <PhosphorIconTrash
                                    v-if="isCompanyManager()"
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    :title="$t('actionsTablesTitle.delete')"
                                    @click="removeSite(item.id)"
                                />
                            </div>
                        </template>

                        <template #table-image="{ item }">
                            <VImg
                                v-if="item"
                                :src="item.image"
                                height="64"
                                width="64"
                                cover
                                class="circle-img"
                                lazy-src="/assets/images/placeholder.png"
                            />
                        </template>
                    </TableData>
                </div>

                <div class="user">
                    <TableData
                        key="userTable"
                        :search="search.user"
                        :items="userTable"
                        :loading="isLoading"
                        :per-page="perPage.tableUser"
                        @pagination="getPaginationDataUser"
                    >
                        <template #table-no-data>
                            <div class="textt-center">
                                {{ t('tableEmptyData.emptyUser') }}
                            </div>
                        </template>

                        <template #table-title>
                            <div class="mx-5 w-100 d-flex align-center justify-space-between">
                                <div class="header-title">
                                    <h3>
                                        {{ t('users.users') }}
                                    </h3>
                                </div>

                                <div class="d-flex">
                                    <div class="search-wrap me-4">
                                        <VTextField
                                            v-model="search.user"
                                            :label="t('users.search')"
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
                                            v-if="isCompanyManager()"
                                            class="text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openAddUserModal"
                                        >
                                            {{ t('users.addUser') }}
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
                                <PhosphorIconPencilSimple
                                    v-if="isCompanyManager()"
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    :title="$t('actionsTablesTitle.edit')"
                                    @click="openEditUserModal(item)"
                                />

                                <PhosphorIconTrash
                                    v-if="isCompanyManager()"
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    :title="$t('actionsTablesTitle.delete')"
                                    @click="removeUser(item.id)"
                                />
                            </div>
                        </template>
                    </TableData>
                </div>
            </div>
        </VContainer>

        <AddProduct />
        <EditProduct />
        <LinkProduct :company-id="companyId" />
        <AddUser />
        <EditUser />
    </NuxtLayout>
</template>
