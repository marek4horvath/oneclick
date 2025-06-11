export class StepsTemplate {
    '@id': string
    '@type': string
    id: string
    name: string
    steps: string[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.steps = []
    }
}
