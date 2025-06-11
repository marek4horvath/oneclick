export class SupplyChain {
    '@id': string
    '@type': string
    id: string
    name: string
    nodes: string[]
    deletedAt: string
    productTemplates: string[]
    nodeTemplates: string[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.nodes = []
        this.deletedAt = ''
        this.productTemplates = []
        this.nodeTemplates = []
    }
}
