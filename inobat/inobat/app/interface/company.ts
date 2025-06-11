export interface CompanyPayload {
    "@id": string,
    "@type": string,
    name: string,
    latitude: number,
    longitude: number,
    id: string,
}

export interface CompanyFullPayload extends CompanyPayload {
    street: undefined|string,
    zip: undefined|string,
    houseNo: undefined|string,
    city: undefined|string,
    country: undefined|string,
    contact: undefined|string,
    email: undefined|string,
    description: undefined|string,
    companyLogo: undefined|string
}