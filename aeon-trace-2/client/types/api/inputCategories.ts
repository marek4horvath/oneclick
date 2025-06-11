import type { Inputs } from "./inputs"

export class InputCategories {
    '@id': string
    '@type': string
    id: string
    name?: string
    inputs?: Inputs[]
    productInputs?: string[]

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.name = ''
        this.productInputs = []
        this.inputs = []
    }
}
