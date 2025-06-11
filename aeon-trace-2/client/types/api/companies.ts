import type { Product } from "./product"
import type { Address } from "./address"
import type { TypeOfTransport } from "./typeOfTransport"
import type { Sites } from "./sites"
import type { Users } from "./users"

export class Companies {
    '@id': string
    '@type': string
    id: string
    name: string
    companyLogo: string
    address: Address
    latitude: number
    longitude: number
    description: string
    logisticsCompany: boolean
    typeOfTransport: TypeOfTransport
    productCompany: boolean
    sites: Sites
    users: Users
    slug: string
    productTemplates: Product[]
    logistics: string[]
    firstName: string
    lastName: string
    phone: string
    roles: string[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.companyLogo = ''
        this.latitude = 0
        this.longitude = 0
        this.description = ''
        this.logisticsCompany = false
        this.productCompany = false
        this.slug = ''
        this.logistics = []
        this.firstName = ''
        this.lastName = ''
        this.phone = ''
        this.roles = []
    }
}
