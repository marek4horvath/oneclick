export interface NodePayload {
    "@context": string,
    "@id": string,
    "@type": string,
    name: string,
    id: string,
    childrens: NodePayload[],
    parents: NodePayload[],
    description: string,
    sort: number
}
