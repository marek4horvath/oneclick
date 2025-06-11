import type { Address } from "./address"

export class Sites {
    '@id': string
    '@type': string
    id: string
    name: string
    address: Address
    latitude: number
    longitude: number

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.latitude = 0
        this.longitude = 0
    }
}
