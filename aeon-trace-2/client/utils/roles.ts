export const Roles = {
    END_USER: 'ROLE_END_USER',
    COMPANY_USER: 'ROLE_COMPANY_USER',
    COMPANY_MANAGER: 'ROLE_COMPANY_MANAGER',
    ADMIN: 'ROLE_ADMIN',
    SUPER_ADMIN: 'ROLE_SUPER_ADMIN',
} as const

export const RoleHierarchy = {
    [Roles.END_USER]: 1,
    [Roles.COMPANY_USER]: 2,
    [Roles.COMPANY_MANAGER]: 3,
    [Roles.ADMIN]: 4,
    [Roles.SUPER_ADMIN]: 5,
}
