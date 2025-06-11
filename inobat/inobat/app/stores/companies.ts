import type { CompanyPayload } from "~/interface/company"

export const useCompanyStore = defineStore('companies', {
    state: () => ({
        companies: [] as CompanyPayload[],
    }),

    getters: {
        getCompanies: (state) => state.companies,

        getCompanyById: (state) => (id: string) => state.companies.find(company => company.id === id),

        getProductsByCompany: (state) => (id: string) => {
            const productStore = useProductStore();

            return productStore.getProductsByCompanyId(id);
        }
    },

    actions: {
        addCompanies(payload: CompanyPayload) {
            this.companies.push(payload)
        },

        deleteCompanies(companyId: string) {
            this.companies = this.companies.filter(company => company.id !== companyId);
        },

        editCompanies(companyId: string, updatedCompany: Partial<CompanyPayload>) {
            const index = this.companies.findIndex(company => company.id === companyId);

            if (index !== -1) {
                this.companies[index] = { ...this.companies[index], ...updatedCompany };
            }

        }
    },

    persist: true,
})
