import type { Companies } from "./api/companies"

export interface CompaniesState {
    companies: Companies[]
    companiesAssignedToSupplyChain: Companies[]
    totalItems: number
}
