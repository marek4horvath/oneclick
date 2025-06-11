export class Batches {
    '@id': string
    '@type': string
    batchType: string[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.batchType = []
    }
}
