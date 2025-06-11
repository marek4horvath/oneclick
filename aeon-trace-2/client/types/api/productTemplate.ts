export interface Company {
    "@context": string
    "@id": string
    "@type": string
    name: string
    latitude: number
    longitude: number
    id: string
}

export interface Step {
    "@context": string
    "@id": string
    "@type": string
    id: string
    name: string
    quantity: number
    measurementType: string
    unitMeasurement: string
    stepImage: string
    batchTypeOfStep: string
    process: string
    parentSteps: string[]
    company: Company
    sort: number
    steps: string[]
    inputs: Input[]
    qrId: number
    parentStepNames: string[]
}

export interface Input {
    "@context": string
    "@id": string
    "@type": string
    id: string
    type: string
    name: string
    sort: number
}

export interface StepsTemplate {
    "@context": string
    "@id": string
    "@type": string
    id: string
    name: string
    steps: Step[]
}

export interface ProductTemplate {
    "@context": string
    "@id": string
    "@type": string
    id: string
    name: string
    productImage: string
    haveDpp: boolean
    companies: Company[]
    stepsTemplate: StepsTemplate
    description: string
    nodes: string[]
}
