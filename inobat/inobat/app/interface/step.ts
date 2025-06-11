import type { CompanyPayload } from "./company"
import type { InputPayload } from "./inputPayload"

export interface StepPayload {
    "@id": string,
    "@type": string,
    id: string,
    name: string,
    quantity: number,
    stepImage: string,
    batchTypeOfStep: string,
    process: string,
    parentStep: StepPayload,
    sort: number,
    steps: StepPayload[],
    inputs: InputPayload[]
}