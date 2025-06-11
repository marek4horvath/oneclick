import type { SupplyChain } from '~/types/api/supplyChains.ts'

export interface SupplyChainState {
    supplyChains: SupplyChain[]
    supplyChain: SupplyChain | null
    totalItems: number
}
