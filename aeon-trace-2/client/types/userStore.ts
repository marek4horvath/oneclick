import type { Users } from "./api/users"

export interface UsersState {
    users: Users[]
    totalItems: number
}
