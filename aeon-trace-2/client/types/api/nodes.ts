import type { Steps } from "./steps"
import type { TreePosition } from "./treePosition"

export class Nodes {
    '@id': string
    '@type': string
    id: string
    name: string
    description: string
    typeOfProcess: string
    parents: string[]
    children: string[]
    siblings: string[]
    supplyChainTemplate: string
    productTemplates: string[]
    steps: Steps[]
    qrId: number
    fromNodeLogistics: string[]
    toNodeLogistics: string[]
    existAssignedDpp: boolean
    existLogisticsAssignedToDpp: boolean
    nodePosition?: TreePosition

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.description = ''
        this.typeOfProcess = ''
        this.parents = []
        this.children = []
        this.siblings = []
        this.supplyChainTemplate = ''
        this.productTemplates = []
        this.steps = []
        this.qrId = 0
        this.fromNodeLogistics = []
        this.toNodeLogistics = []
        this.existAssignedDpp = false
        this.existLogisticsAssignedToDpp = false
    }
}
