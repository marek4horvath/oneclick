import type { NodePayload } from "./node";

export interface SupplyChainPayload {
    "@context": string,
    "@id": string,
    "@type": string,
    name: string,
    id: string,
    nodes: NodePayload[]
}
