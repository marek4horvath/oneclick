export class TreePosition {
    '@id': string
    '@type': string
    id: string
    x: number
    y: number

    constructor() {
        this['@id'] = ''
        this['@type'] = ''
        this.id = ''
        this.x = 0
        this.y = 0
    }
}
