import type { SupplyChain } from '~/types/api/supplyChains.ts'
import type { HydraCollection } from "@/types/api/hydra"
import type { SupplyChainTemplate } from "@/types/api/supplyChainTemplate"

export const useSupplyChainStore = defineStore('supplyChain', {
    state: () => ({
        supplyChainTemplates: [] as SupplyChainTemplate[],
        supplyChainTemplate: Object as SupplyChainTemplate,
        supplyChainTemplatesItemsCount: 0,
    }),

    getters: {
        getSupplyChainTemplates: (state): SupplyChain[] => state.supplyChainTemplates,

        getSupplyChainTemplatesItemsCount: (state): number => state.supplyChainTemplatesItemsCount,

        getSupplyChainTemplate: (state): SupplyChain => state.supplyChainTemplate,

        getSupplyChainById: state => (supplyChainId?: string): SupplyChain | undefined =>
            state.supplyChainTemplates.find((supplyChain: any) => supplyChain.id === supplyChainId),
    },

    actions: {
        addSupplyChainTemplate(supplyChainTemplate: SupplyChainTemplate): void {
            this.supplyChainTemplates.push(supplyChainTemplate)
        },

        setSupplyChainTemplates(supplyChainTemplates: SupplyChainTemplate[]): void {
            this.supplyChainTemplates = supplyChainTemplates
        },

        setSupplyChainTemplate(supplyChain: SupplyChain) {
            this.supplyChainTemplate = supplyChain
        },

        setSupplyChainTemplatesItemsCount(supplyChainTemplatesItemsCount: number): void {
            this.supplyChainTemplatesItemsCount = supplyChainTemplatesItemsCount
        },

        async fetchSupplyChainTemplates(page: number = 1, itemsPerPage: number = 20, orderName?: string, orderValue?: string, searchName?: string, deletedAt: string = 'null') {
            const getOrderParam = (key: string) => `order[${key}]`

            const { $axios } = useNuxtApp()

            try {
                const response: { data: HydraCollection<SupplyChainTemplate[]> } = await $axios.get(
                    '/supply_chain_templates',
                    {
                        params: {
                            page,
                            itemsPerPage,
                            name: searchName,
                            deletedAt,
                            ...(orderName && orderValue ? { [getOrderParam(orderName)]: orderValue } : {}),
                        },
                    },
                )

                this.setSupplyChainTemplates(response.data['hydra:member'])
                this.setSupplyChainTemplatesItemsCount(response.data['hydra:totalItems'])

                return response.data['hydra:member']
            } catch (error) {
                console.info(error)
            }
        },

        async fetchSupplyChainTemplate(id: string) {
            try {
                const { $axios } = useNuxtApp()
                const response: { data: SupplyChainTemplate } = await $axios.get(`/supply_chain_templates/${id}`)

                this.setSupplyChainTemplate(response.data)

                return response.data
            } catch (error) {
                console.info(error)
            }
        },

        async fetchSupplyChainTemplateDetail(id: string) {
            try {
                const { $axios } = useNuxtApp()
                const response: { data: SupplyChainTemplate } = await $axios.get(`/supply_chain_templates/detail/${id}`)

                this.setSupplyChainTemplate(response.data)

                return response.data
            } catch (error) {
                console.info(error)
            }
        },

        async createSupplyChain(name: string, products: string[]) {
            try {
                const { $axios } = useNuxtApp()

                const productTemplateIds = products.map((product: any) => {
                    return `/api/product_templates/${product}`
                })

                const response: { data: SupplyChainTemplate } = await $axios.post('/supply_chain_templates', {
                    name,
                    productTemplates: productTemplateIds,
                })

                this.addSupplyChainTemplate(response.data)

                return response.data
            } catch (error) {
                console.info(error)
            }
        },

        async fetchSupplyChainTemplatesVerify(id: string, itemsPerPage: number = 30) {
            const { $axios } = useNuxtApp()

            try {
                const response: { data: HydraCollection<SupplyChainTemplate[]> } = await $axios.get(
                    `/supply_chain_templates/verify/${id}`,
                    {
                        params: {
                            itemsPerPage,
                        },
                    },
                )

                this.setSupplyChainTemplates(response.data)

                return response.data
            } catch (error) {
                console.info(error)
            }
        },

        async updateSupplyChainTemplate(id: string, updatedData: Partial<SupplyChainTemplate>) {
            try {
                const { $axios } = useNuxtApp()

                const response: { data: SupplyChainTemplate } = await $axios.patch(
                    `/supply_chain_templates/${id}`,
                    updatedData,
                    { headers: { 'Content-Type': 'application/merge-patch+json' } },
                )

                this.supplyChainTemplates = this.supplyChainTemplates.map(sc =>
                    sc.id === id ? { ...sc, ...response.data } : sc,
                )

                return response.data
            } catch (error) {
                console.info(error)
            }
        },

        async removeSupplyChainTemplate(id: string) {
            try {
                const { $axios } = useNuxtApp()
                const response = await $axios.deleteRequest(`/supply_chain_templates/${id}`)
                if (response) {
                    this.supplyChainTemplates = this.supplyChainTemplates.filter(sc => sc.id !== id)
                }

                return response
            } catch (error) {
                console.info(error)
            }
        },
    },

    persist: {
        storage: localStorage,
    },
})
