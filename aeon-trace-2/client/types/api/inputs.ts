import type { InputCategories } from "./inputCategories"

export class Inputs {
    '@id': string
    '@type': string
    id: string
    name: string
    type: string[]
    step: string
    logisticsTemplate: string
    inputCategories?: InputCategories
    sort: number

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.step = ''
        this.type = []
        this.logisticsTemplate = ''
        this.sort = 0
    }
}
