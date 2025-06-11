export class Address {
    '@id': string
    '@type': string
    id: string
    street: string
    city: string
    postcode: string
    houseNo: string
    country: string

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.street = ''
        this.city = ''
        this.postcode = ''
        this.houseNo = ''
        this.country = ''
    }
}
