import type { StepPayload } from "./step";

export interface StepTemplatePayload {
    "@id": string,
    "@type": string,
    id: string,
    name: string,
    steps: StepPayload[],
}