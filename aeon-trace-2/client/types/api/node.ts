export interface Node {
    "@context": string
    "@id": string
    "@type": string
    id: string
    name: string
    description: string
    typeOfProcess: string
    parents: string[]
    children: string[]
    siblings: string[]
    supplyChainTemplate: string
    productTemplates: string[]
    nodeTemplate: string
    steps: any[]
    qrId: number
    fromNodeLogistics: string[]
    toNodeLogistics: string[]
    countDpp: Record<string, number>
    countLogistics: Record<string, string[]>
    existAssignedDpp: boolean
    existLogisticsAssignedToDpp: boolean
    nodePosition: {
        "@context": string
        "@id": string
        "@type": string
        id: string
        x: number
        y: number
    }
    companiesFromProductTemplates: string[][]
}
