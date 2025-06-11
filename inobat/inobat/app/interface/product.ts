import type { CompanyPayload } from "./company"
import type { StepTemplatePayload } from "./stepTemplate";

export interface ProductPayload {
    "@id": string,
    "@type": string,
    id: string,
    name: string,
    productImage: string,
    haveDpp: boolean,
    companies: CompanyPayload[],
    stepsTemplate: StepTemplatePayload,
    description: string,
    nodes: string[]|null,
}
