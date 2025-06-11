import type { Inputs } from "./inputs"
import type { TreePosition } from "./treePosition"
import type { Nodes } from "./nodes"

export class Steps {
    '@id': string
    '@type': string
    id: string
    name: string
    quantity: number
    stepImage: string
    batchTypeOfStep: string
    process: string
    stepsTemplate: string
    parentSteps: string[]
    company: string
    productTemplate: string
    sort: number
    qrImage: string
    steps: string[]
    inputs: Inputs[]
    nodes: Nodes[]
    qrId: number
    dpps: string[]
    stepPosition?: TreePosition

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.steps = []
        this.quantity = 0
        this.stepImage = ''
        this.batchTypeOfStep = ''
        this.process = ''
        this.stepsTemplate = ''
        this.parentSteps = []
        this.company = ''
        this.productTemplate = ''
        this.sort = 0
        this.qrImage = ''
        this.qrId = 0
        this.dpps = []
        this.inputs = []
        this.nodes = []
    }
}
