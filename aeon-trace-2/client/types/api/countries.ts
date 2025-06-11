export class Countries {
    '@id': string
    '@type': string
    countries: string[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.countries = []
    }
}
