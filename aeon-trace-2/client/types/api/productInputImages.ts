export class ProductInputImages {
    '@id': string
    '@type': string
    file: string
    id: string
    image: string
    input: string

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.file = ''
        this.image = ''
        this.input = ''
    }
}
