import { useAuthStore } from '@/stores/auth'
import { RoleHierarchy, Roles } from '@/utils/roles'

export function useRoleAccess() {
    const authStore = useAuthStore()

    const hasAtLeastRole = (requiredRole: Roles): boolean => {
        const userRoles = authStore.getRoles || []

        const userMaxRoleLevel = Math.max(
            ...userRoles.map(role => RoleHierarchy[role as Roles] || 0),
        )

        return userMaxRoleLevel >= RoleHierarchy[requiredRole]
    }

    const isEndUser = () => hasAtLeastRole(Roles.END_USER)
    const isCompanyUser = () => hasAtLeastRole(Roles.COMPANY_USER)
    const isCompanyManager = () => hasAtLeastRole(Roles.COMPANY_MANAGER)
    const isAdmin = () => hasAtLeastRole(Roles.ADMIN)
    const isSuperAdmin = () => hasAtLeastRole(Roles.SUPER_ADMIN)

    return {
        hasAtLeastRole,
        isEndUser,
        isCompanyUser,
        isCompanyManager,
        isAdmin,
        isSuperAdmin,
    }
}
