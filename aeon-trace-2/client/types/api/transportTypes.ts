export class TransportTypes {
    '@id': string
    '@type': string
    id: string
    name: string
    logisticsTemplateData: string[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.logisticsTemplateData = []
    }
}
