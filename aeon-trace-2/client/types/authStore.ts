export interface AuthState {
    accessToken: string | null
    refreshToken: string | null
    email: string | null
    users: []
    roles: string[]
    company: string | undefined
}
