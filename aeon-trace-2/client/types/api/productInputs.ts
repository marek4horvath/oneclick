export class ProductInputs {
    '@id': string
    '@type': string
    id: string
    productStep: string
    textValue: string
    name: string
    type: string
    numericalValue: number
    dateTimeTo: string // or Date, depending on your usage
    dateTimeFrom: string // or Date, depending on your usage
    latitudeValue: number
    longitudeValue: number
    textAreaValue: string
    document: string
    image: string
    images: Array<{
        "@id": string
        "@type": string
        id: string
        image: string
    }>

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.productStep = ''
        this.textValue = ''
        this.name = ''
        this.type = ''
        this.numericalValue = 0
        this.dateTimeTo = ''
        this.dateTimeFrom = ''
        this.latitudeValue = 0
        this.longitudeValue = 0
        this.textAreaValue = ''
        this.document = ''
        this.image = ''
        this.images = []
    }
}
