export interface Product {
    '@id': string
    '@type': string
    id: string
    modelId: string
    name: string
    address: string
    description: string
    steps: string[]
    image: string | null
    company: []
    stepsTemplate: {
        id: string
        name: string
        steps: string[]
    } | null
}
