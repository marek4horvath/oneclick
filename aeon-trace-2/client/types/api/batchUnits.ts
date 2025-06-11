export class BatchUnits {
    '@id': string
    '@type': string
    batchType: string[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.batchType = []
    }
}
