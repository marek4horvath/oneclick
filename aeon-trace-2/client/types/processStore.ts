import type { Process } from "./api/process"

export interface ProcessState {
    process: Process[]
    unassigned: Process[]
    totalItems: number
    unassignedTotalItems: number
}
