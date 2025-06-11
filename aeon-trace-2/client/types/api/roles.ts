export class Roles {
    '@id': string
    '@type': string
    roles: string []

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.roles = []
    }
}
