import { defineStore } from 'pinia'
import type { CompaniesState } from '../types/companiesStore'
import type { Companies } from '~/types/api/companies'

export const useCompaniesStore = defineStore('companies', {
    state: (): CompaniesState => ({
        companies: [],
        companiesAssignedToSupplyChain: [],
        totalItems: 0,
    }),

    getters: {
        getCompanies: (state): Companies[] => state.companies,
        getTotalItems: (state): number => state.totalItems,
        getCompanyById: state => (id: string) => state.companies.find(company => company.id === id),
        getCompaniesAssignedToSupplyChain: (state): Companies[] => state.companiesAssignedToSupplyChain,
    },

    actions: {

        setCompanies(companies: Companies[]) {
            this.companies = companies
        },

        setTotalItems(total: number) {
            this.totalItems = total
        },

        setCompaniesAssignedToSupplyChain(companies: Companies[]) {
            this.companiesAssignedToSupplyChain = companies
        },

        async fetchCompanies(page?: number, itemsPerPage?: number, searchName?: string, logisticsCompany?: boolean, productCompany?: boolean, orderName?: string, orderValue?: string) {
            const { $axios } = useNuxtApp()

            try {
                const params: Record<string, any> = {}

                if (page) {
                    params.page = page
                }

                if (itemsPerPage) {
                    params.itemsPerPage = itemsPerPage
                }

                if (searchName) {
                    params.name = searchName
                }

                if (logisticsCompany) {
                    params.logisticsCompany = logisticsCompany
                }

                if (productCompany) {
                    params.productCompany = productCompany
                }

                if (orderName && orderValue) {
                    params[getOrderParam(orderName)] = orderValue
                }

                const response = (await $axios.get('/companies', { params }))?.data || []

                this.setCompanies(response['hydra:member'])
                this.setTotalItems(response['hydra:totalItems'])
            } catch (error) {
                console.error(error)
            }
        },

        async fetchCompaniesListing(page?: number, itemsPerPage?: number, searchName?: string, logisticsCompany?: boolean, productCompany?: boolean, orderName?: string, orderValue?: string, companyId?: string) {
            const { $axios } = useNuxtApp()

            try {
                const params: Record<string, any> = {}

                if (page) {
                    params.page = page
                }

                if (itemsPerPage) {
                    params.itemsPerPage = itemsPerPage
                }

                if (searchName) {
                    params.name = searchName
                }

                if (logisticsCompany) {
                    params.logisticsCompany = logisticsCompany
                }

                if (productCompany) {
                    params.productCompany = productCompany
                }

                if (orderName && orderValue) {
                    params[getOrderParam(orderName)] = orderValue
                }

                if (companyId) {
                    params['id'] = companyId
                }

                const response = (await $axios.get('/companies/listing', { params }))?.data || []

                this.setCompanies(response['hydra:member'])
                this.setTotalItems(response['hydra:totalItems'])

                return response['hydra:member']
            } catch (error) {
                console.error(error)
            }
        },

        async fetchCompanyById(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response = (await $axios.get(`/companies/${id}`))?.data

                if (response) {
                    const existingCompanyIndex = this.companies.findIndex(company => company.id === id)

                    if (existingCompanyIndex !== -1) {
                        this.companies[existingCompanyIndex] = response
                    } else {
                        this.companies.push(response)
                    }
                }

                return response
            } catch (error) {
                console.error(error)

                return null
            }
        },

        async createCompany(companyData: Companies) {
            const { $axios } = useNuxtApp()
            try {
                const response = await $axios.post('/companies/add-company', companyData)

                this.companies.push(response.data)

                return response.data
            } catch (error) {
                console.error(error)

                return null
            }
        },

        async updateCompany(id: string, updatedData: Partial<Companies>) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.patch(`/companies/${id}`, updatedData, {
                    headers: { 'Content-Type': 'application/merge-patch+json' },
                })

                this.companies = this.companies.map(company =>
                    company.id === id ? { ...company, ...response.data } : company,
                )

                return response.data
            } catch (error) {
                console.error(error)

                return null
            }
        },

        async deleteCompany(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.deleteRequest(`/companies/${id}`)

                if (!response) {
                    return
                }

                this.companies = this.companies.filter(company => company.id !== id)

                return response
            } catch (error) {
                console.error(error)
            }
        },

        async inviteCompany(email: string) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.post('/companies/invite-company', {
                    email,
                })

                return response.data
            } catch (error) {
                console.error(error)

                return null
            }
        },

        async registerCompany(companyData: any) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.post('/api/companies/register-company', companyData)

                return response.data
            } catch (error) {
                console.error(error)

                return null
            }
        },

        async uploadCompanyLogo(id: string, logo: File) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.post(`/companies/${id}/company_logo`, logo, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })

                return response.data
            } catch (error) {
                console.error(error)

                return null
            }
        },

        async deleteCompanyLogo(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.deleteRequest(`/api/companies/${id}/company_logo`)

                return response
            } catch (error) {
                console.error(error)

                return null
            }
        },

        async uploadFirstCompanyLogo(id: string, logo: File) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.post(`/api/first_image/companies/${id}/company_logo`, logo, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })

                return response.data
            } catch (error) {
                console.error(error)

                return null
            }
        },

    },
})
