<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { axiosIns } from '@/plugins/axios'

interface SupplyItem {
    id: number
    name: string
    nodes: []
    actions: object[]
    numberNodes: number
}

interface Pagination {
    currentPage: string
    next: string | null
    prev: string | null
    first: string | null
    last: string | null
    totalItems: number
}

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const regex = /(?:\?|&)page=(\d+)/
const isLoading = ref<boolean>(true)

const perPage = ref<{ [key: string]: number }>({
    tableSupply: 20,
})

const perPageOptions = ref([5, 10, 20, 50, 100])
const tableDataSupply = ref<SupplyItem[]>([])

const paginationDataSupply = ref<Pagination>({
    currentPage: '',
    next: null,
    prev: null,
    first: null,
    last: null,
    totalItems: 0,
})

const tableHeadersSupply = ref([
    { text: t('supplyChain.tableHeaders.name'), key: 'name', sortable: true, sortAsc: true },
    { text: t('supplyChain.tableHeaders.numberNodes'), key: 'numberNodes', sortable: true, sortAsc: true },
    { text: t('supplyChain.tableHeaders.actions'), key: 'actions', sortable: false, sortAsc: false },
])

const actions = [
    { icon: 'fa-solid fa-eye', actionName: 'detail', to: '' },
]

const fetchSupplyData = async (page?: string, itemsPerPage?: number) => {
    const urlRequest = page ? `supply_chain_templates?page=${page}&itemsPerPage=${itemsPerPage}` : `supply_chain_templates?page=1&itemsPerPage=${itemsPerPage}`
    const getSupplyChainResponse = await axiosIns.get(urlRequest)

    if (!getSupplyChainResponse) {
        return
    }

    tableDataSupply.value = getSupplyChainResponse.data['hydra:member'].filter((item: any) => !item.deletedAt)

    const viewData = getSupplyChainResponse.data['hydra:view']

    if (viewData && viewData['@id']?.match(regex)) {
        paginationDataSupply.value = {
            currentPage: viewData['@id']?.match(regex)[1],
            next: viewData['hydra:next']?.match(regex)[1],
            prev: viewData['hydra:previous']?.match(regex)[1],
            first: viewData['hydra:first']?.match(regex)[1],
            last: viewData['hydra:last']?.match(regex)[1],
            totalItems: getSupplyChainResponse.data['hydra:totalItems'],
        }
    }

    tableDataSupply.value.forEach(item => {
        item.numberNodes = item.nodes?.length

        const linkDetail = `/data-sharing-policy/${item.id}`

        item.actions = actions.map((action: any) => {
            if (action.actionName === 'detail') {
                return { ...action, to: linkDetail }
            }

            return action
        })
    })

    isLoading.value = false
}

const fetchNextPageSupplyData = () => {
    if (paginationDataSupply.value.next) {
        const pageNext = paginationDataSupply.value.next

        fetchSupplyData(pageNext, perPage.value.tableSupply)
    }
}

const fetchPrevPageSupplyData = () => {
    if (paginationDataSupply.value.prev) {
        const pagePrev = paginationDataSupply.value.prev

        fetchSupplyData(pagePrev, perPage.value.tableSupply)
    }
}

const handleRowClickSupplyChain = (rowData: any) => {
    const actionData = rowData.actions.filter((action: any) => action.actionName === 'detail')
    if (actionData.length > 0 && actionData[0].to) {
        router.push(actionData[0].to)
    }
}

const countRowPage = async (countRow: number, tableKey: string) => {
    const currentCountRows = route.query.countRows
        ? JSON.parse(decodeURIComponent(route.query.countRows as string))
        : {}

    currentCountRows[tableKey] = countRow

    const updatedCountRows = encodeURIComponent(JSON.stringify(currentCountRows))

    router.replace({
        ...route,
        query: {
            ...route.query,
            countRows: updatedCountRows,
        },
    })

    if (Object.keys(currentCountRows).length > 0) {
        tableDataSupply.value = []
        await fetchSupplyData(undefined, currentCountRows.tableSupply)
    }
}

onMounted(async () => {
    await fetchSupplyData(undefined, perPage.value.tableSupply)

    const countRows = {
        tableSupply: 20,
    }

    if (!route.query.countRows) {
        router.replace({
            ...route,
            query: {
                ...route.query,
                countRows: encodeURIComponent(JSON.stringify(countRows)),
            },
        })
    }

    const currentCountRows = route.query.countRows
        ? JSON.parse(decodeURIComponent(route.query.countRows as string))
        : {}

    if (Object.keys(currentCountRows).length > 0) {
        perPage.value = currentCountRows
        await fetchSupplyData(undefined, perPage.value.tableSupply)
    }
})

watch(tableDataSupply, newVal => {
    if (newVal.length !== 0) {
        tableHeadersSupply.value = tableHeadersSupply.value.filter(header => header.key !== 'actions')

        return
    }

    if (!tableHeadersSupply.value.some(header => header.key === 'actions')) {
        tableHeadersSupply.value.push({ text: 'Actions', key: 'actions', sortable: false, sortAsc: false })
    }
}, { immediate: true })
</script>

<template>
    <div>
        <DashBoard>
            <template #content>
                <div class="companies">
                    <div class="page-header">
                        <h1>{{ t("dataSharingPolicy.header") }}</h1>
                    </div>
                </div>

                <TableComponent
                    :data="tableDataSupply"
                    :headers="tableHeadersSupply"
                    :per-page="perPage.tableSupply"
                    :per-page-options="perPageOptions"
                    :is-mass-action="false"
                    :header="t('supplyChain.header')"
                    :row-click-handler="handleRowClickSupplyChain"
                    is-popup
                    id-popup="edit-popup"
                    :placeholder="t('supplyChain.search')"
                    :pagination="paginationDataSupply"
                    show-filter
                    class="supply-tab"
                    @fetch-next-page="fetchNextPageSupplyData"
                    @fetch-prev-page="fetchPrevPageSupplyData"
                    @get-row-per-page="(countRow) => countRowPage(countRow, 'tableSupply')"
                />
            </template>
        </DashBoard>
    </div>
</template>

<route lang="yaml">
meta:
  requiresAuth: true
    </route>
