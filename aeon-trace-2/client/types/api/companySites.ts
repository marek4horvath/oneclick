import type { Address } from "./address"
import type { SiteImages } from "./siteImages"

export class CompanySite {
    '@id': string
    '@type': string
    id: string
    name: string
    company: string
    address: Address
    latitude: number
    longitude: number
    siteImages: SiteImages[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.company = ''
        this.latitude = 0
        this.longitude = 0
        this.siteImages = []
    }
}
