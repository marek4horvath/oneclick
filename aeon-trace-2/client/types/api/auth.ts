export interface LoginPayload {
    email: string
    password: string
}

export interface LoginResponse {
    token: string
    refresh_token: string
}

export interface Registration {
    email: string
    firstName: string
    lastName: string
    company: string
    roles: string[]
}

export interface FirstLogin {
    token: string
    password: string
}

export interface InviteCompanyRegistration {
    name: string
    street: string
    houseNo: string
    city: string
    postcode: string
    country: string | null
    email: string
    phone: string
    password: string
    token: string
}
