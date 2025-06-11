import type { Companies } from "./companies"

export class Users {
    '@id': string
    '@type': string
    id: string
    email: string
    firstName: string
    lastName: string
    phone: string
    roles: string[]
    company?: Companies[]
    userAvatar?: string

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.email = ''
        this.firstName = ''
        this.lastName = ''
        this.phone = ''
        this.roles = []
    }
}
