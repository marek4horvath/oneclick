export class HistoryInputs {
    '@id': string
    '@type': string
    id: string
    name: string
    type: string
    productInput: string
    images: array
    unitMeasurement: string
    measurementType: string
    measurementValue: number
    automaticCalculation: boolean
    locked: boolean
    updatedAt: string
    updatedBy: object
    version: number

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.type = ''
        this.productInput = ''
        this.images = []
        this.unitMeasurement = ''
        this.measurementType = ''
        this.measurementValue = 0
        this.automaticCalculation = false
        this.locked = false
        this.updatedAt = ''
        this.updatedBy = {}
        this.version = 0
    }
}
