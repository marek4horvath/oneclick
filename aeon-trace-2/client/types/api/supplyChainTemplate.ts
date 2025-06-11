export interface SupplyChainTemplate {
    "@id": string
    "@type": string
    id: string
    name: string
    nodes: string[]
    deletedAt: string | null
    productTemplates: string[]
    nodeTemplates: string[]
}
