<script setup lang="ts">
import { useI18n } from "vue-i18n"
import { PhosphorIconEye, PhosphorIconPencil, PhosphorIconTrash } from '#components'
import type { ProductsState } from '@/types/productsStore'
import type { InputCategoryState } from '@/types/inputCategoryStore'
import type { TableData } from '@/types/tableData'
import { getPage, getTotalPerPage, getUrlQueryCountRows, setPage, setUrlQueryCountRows } from '@/helpers/urlQueryCountRows'
import { debounce } from '@/helpers/debounce'
import AddProduct from '@/dialogs/products/add-product.vue'
import EditProduct from '@/dialogs/products/edit-product.vue'
import LinkProduct from '@/dialogs/products/link-product.vue'
import DetailCategory from '@/dialogs/categories/detail-category.vue'
import CreateInputCategory from '@/dialogs/categories/create-input-category.vue'
import EditInputCategory from '@/dialogs/categories/edit-input-category.vue'
import { useProductsStore } from "~/stores/products.ts"
import { useInputCategoriesStore } from "~/stores/inputCategories.ts"

definePageMeta({
    title: 'page.products.title',
    name: 'index-products',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const isLoading = ref<boolean>(true)
const productsStore = useProductsStore()
const inputCategoriesStore = useInputCategoriesStore()
const products = ref<ProductsState[]>()
const inputCategories = ref<InputCategoryState[]>()

const search = ref({
    product: '',
    category: '',
})

const currentCountRows = route.query.countRows
    ? JSON.parse(decodeURIComponent(route.query.countRows as string))
    : {}

const perPage = ref<{ [key: string]: number }>({
    tableProduct: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableProduct : getTotalPerPage('tableProduct') ?? 20,
    tableCategory: Object.keys(currentCountRows).length > 0 ? currentCountRows.tableCategory : getTotalPerPage('tableCategory') ?? 20,
})

const productTable = ref<TableData>({
    headers: [
        { key: 'image', title: '', sortable: false },
        { key: 'name', title: t('productTableHeader.productName'), sortable: true },
        { key: 'companiesNames', title: t('productTableHeader.company'), width: '401px', sortable: false },
        { key: 'totalSteps', title: t('productTableHeader.numberOfStep'), sortable: true },
        { key: 'totalInputs', title: t('productTableHeader.numberOfInput'), sortable: true },
        { key: 'actions', title: t('productTableHeader.actions'), width: '200px', align: 'start', sortable: false },
    ],
    idTable: 'productTable',
    totalItems: 0,
    data: [],
})

const categoryTable = ref<TableData>({
    headers: [
        { key: '', title: '', sortable: false },
        { key: 'name', title: t('inputCategoryTableHeader.name'), sortable: true },
        { key: 'inputs', title: t('inputCategoryTableHeader.numberOfInput'), sortable: true },
        { key: 'inputsName', title: t('inputCategoryTableHeader.inputs'), width: '400px', sortable: false },
        { key: 'actions', title: t('inputCategoryTableHeader.actions'), width: '250px', align: 'center', sortable: false },
    ],
    idTable: 'categoryTable',
    totalItems: 0,
    data: [],
})

const updateProductsTable = () => {
    productTable.value.totalItems = productsStore.totalItems
    productTable.value.data = products.value.map((product: any) => {
        return {
            ...product,
            image: product.productImage
                ? `${backendUrl.value}/media/product_images/${product.productImage}`
                : null,
        }
    })
}

const fetchProductData = async (page?: number, itemsPerPage?: number, searchName?: string, key?: string, order?: string) => {
    try {
        const productTemplates = await productsStore.fetchProductsListing(page, itemsPerPage, searchName, key, order)

        products.value = productsStore.getProducts

        if (productsStore.getProducts?.length === 0) {
            products.value = productTemplates
        }

        updateProductsTable()
    } finally {
        isLoading.value = false
    }
}

const updateInputCategoriesTable = () => {
    categoryTable.value.totalItems = inputCategoriesStore.totalItems

    categoryTable.value.data = inputCategories.value.map((category: any) => {
        const inputsName = category.inputs && category.inputs.length > 0
            ? category.inputs.map((input: any) => input.name).join(', ')
            : '----'

        return {
            ...category,
            inputs: category.inputs?.length || 0,
            productInputs: category.inputs || [],
            inputsName,
        }
    })
}

const fetchInputCategoryData = async (page?: number, itemsPerPage?: number, searchName?: string, key?: string, order?: string) => {
    isLoading.value = true
    try {
        await inputCategoriesStore.fetchInputCategories(page, itemsPerPage, searchName, key, order)
    } finally {
        isLoading.value = false
    }

    inputCategories.value = inputCategoriesStore.getInputCategories
    updateInputCategoriesTable()
}

const debouncedFetchProductData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]
    let sortKey

    if (sort?.key === 'totalSteps') {
        sortKey = 'steps'
    }

    if (sort?.key === 'totalInputs') {
        sortKey = 'inputs'
    }

    fetchProductData(pagination.page, pagination.itemsPerPage, pagination.search, sortKey, sort?.order)
}, 500)

const getPaginationDataProduct = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableProduct', router, route)
    setPage('tableProduct', pagination.page)
    productTable.value.data = []
    perPage.value.tableProduct = pagination.itemsPerPage
    debouncedFetchProductData(pagination)

    isLoading.value = false
}

const debouncedFetchCategoryData = debounce((pagination: any) => {
    const sort = pagination?.sortBy?.[0]

    fetchInputCategoryData(pagination.page, pagination.itemsPerPage, pagination.search, sort?.key, sort?.order)
}, 500)

const getPaginationDataCategory = (pagination: any) => {
    isLoading.value = true
    setUrlQueryCountRows(pagination.itemsPerPage, 'tableCategory', router, route)
    setPage('tableCategory', pagination.page)
    categoryTable.value.data = []
    perPage.value.tableProduct = pagination.itemsPerPage
    debouncedFetchCategoryData(pagination)

    isLoading.value = false
}

const removeProduct = async (product: Product) => {
    const response = await productsStore.deleteProduct(product.id)

    if (!response) {
        return
    }

    const countRows = getUrlQueryCountRows('tableProduct', route)

    fetchProductData(1, countRows)
}

const removeInputCategory = async (category: InputCategory) => {
    const response = await inputCategoriesStore.deleteInputCategory(category.id)

    if (!response) {
        return
    }

    const countRows = getUrlQueryCountRows('tableCategory', route)

    fetchInputCategoryData(1, countRows)
}

const detailProduct = (data: any) => {
    router.push(`/products/detail/${data.id}`)
}

const openAddProductModal = () => {
    $event('openAddProductModal')
}

const openEditProductModal = (product: any) => {
    $event('openEditProductModal', product)
}

const openLinkProductModal = () => {
    $event('openLinkProductModal')
}

const openCreateInputCategoryModal = () => {
    $event('openCreateInputCategoryModal')
}

const openDetailCategoryModal = (category: any) => {
    $event('openDetailCategoryModal', category)
}

const openEditInputCategoryModal = (category: any) => {
    $event('openEditInputCategoryModal', category)
}

const handleRowClick = (rowData: any) => {
    if (rowData?.idCompany) {
        router.push(`/products/detail/${rowData?.idCompany}/${rowData.id}`)
    } else {
        router.push(`/products/detail/${rowData.id}`)
    }
}

const handleRowClickCategory = (rowData: any) => {
    $event('openDetailCategoryModal', rowData)
}

$listen('handleCategorySubmitted', async () => {
    const page = getPage('tableCategory') || 1
    const itemsPerPage = getTotalPerPage('tableProduct') || 20

    await fetchInputCategoryData(page, itemsPerPage)
})

$listen('handleLinkProductSubmitted', async () => {
    const page = getPage('tableProduct') || 1
    const itemsPerPage = getTotalPerPage('tableProduct') || 20

    await fetchProductData(page, itemsPerPage)
})

watch(() => productsStore.getProducts, () => {
    products.value = productsStore.getProducts

    updateProductsTable()
}, { deep: true })
</script>

<template>
    <NuxtLayout>
        <VContainer fluid>
            <div class="product">
                <TableData
                    key="productTable"
                    :search="search.product"
                    :items="productTable"
                    :is-delete="false"
                    :loading="isLoading"
                    :per-page="perPage.tableProduct"
                    @pagination="getPaginationDataProduct"
                    @set-row-data="handleRowClick"
                >
                    <template #table-title>
                        <div class="w-100 d-flex align-center justify-space-between">
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
                                        class="text-uppercase ms-2"
                                        color="#26A69A"
                                        variant="flat"
                                        size="large"
                                        @click="openAddProductModal"
                                    >
                                        {{ t('products.addProduct') }}
                                    </VBtn>
                                    <VBtn
                                        class="text-uppercase ms-2"
                                        color="#26A69A"
                                        variant="flat"
                                        size="large"
                                        @click="openLinkProductModal"
                                    >
                                        {{ t('products.linkProduct') }}
                                    </VBtn>
                                    <VBtn
                                        class="text-uppercase ms-2"
                                        color="#26A69A"
                                        variant="flat"
                                        size="large"
                                        @click="openCreateInputCategoryModal"
                                    >
                                        {{ t('products.createInputCategoty') }}
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
                            <VBtn
                                variant="plain"
                                class="cursor-pointer"
                                size="x-small"
                                :title="$t('actionsTablesTitle.detail')"
                                @click="detailProduct(item)"
                            >
                                <PhosphorIconEye
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                />
                            </VBtn>
                            <VBtn
                                variant="plain"
                                class="cursor-pointer"
                                size="x-small"
                                :title="$t('actionsTablesTitle.edit')"
                                @click="openEditProductModal(item)"
                            >
                                <PhosphorIconPencil
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                />
                            </VBtn>
                            <VBtn
                                v-if="!item.haveDpp"
                                variant="plain"
                                class="cursor-pointer"
                                size="x-small"
                                :title="$t('actionsTablesTitle.delete')"
                                @click="removeProduct(item)"
                            >
                                <PhosphorIconTrash
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                />
                            </VBtn>
                        </div>
                    </template>

                    <template #table-image="{ item }">
                        <VImg
                            v-if="item"
                            :src="item.image"
                            height="64"
                            width="64"
                            contain
                            class="circle-img ma-2"
                            lazy-src="/assets/images/placeholder.png"
                        />
                    </template>
                </TableData>
            </div>

            <div class="input-category">
                <TableData
                    key="categoryTable"
                    :search="search.category"
                    :items="categoryTable"
                    :is-delete="false"
                    :loading="isLoading"
                    :per-page="perPage.tableCategory"
                    @pagination="getPaginationDataCategory"
                    @set-row-data="handleRowClickCategory"
                >
                    <template #table-title>
                        <div class="w-100 d-flex align-center justify-space-between">
                            <div class="header-title">
                                <h3>
                                    {{ t('category.category') }}
                                </h3>
                            </div>

                            <div class="d-flex">
                                <div class="search-wrap">
                                    <VTextField
                                        v-model="search.category"
                                        :label="t('category.search')"
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
                            </div>
                        </div>
                    </template>

                    <template #table-actions="{ item }">
                        <div
                            v-if="item"
                            class="actions"
                        >
                            <VBtn
                                variant="plain"
                                class="cursor-pointer"
                                size="x-small"
                                @click="openDetailCategoryModal(item)"
                            >
                                <PhosphorIconEye
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                />
                            </VBtn>

                            <VBtn
                                variant="plain"
                                class="cursor-pointer"
                                size="x-small"
                                @click="openEditInputCategoryModal(item)"
                            >
                                <PhosphorIconPencil
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                />
                            </VBtn>

                            <VBtn
                                v-if="!item.haveDpp"
                                variant="plain"
                                class="cursor-pointer"
                                size="x-small"
                                @click="removeInputCategory(item)"
                            >
                                <PhosphorIconTrash
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                />
                            </VBtn>
                        </div>
                    </template>
                </TableData>
            </div>
        </VContainer>

        <AddProduct />
        <EditProduct />
        <LinkProduct />
        <CreateInputCategory />
        <EditInputCategory />
        <DetailCategory />
    </NuxtLayout>
</template>
