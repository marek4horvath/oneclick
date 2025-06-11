import { camelCase } from "lodash"
import type { RouteRecord } from "vue-router"

const getPageHeader = () => {
    const router = useRouter()

    try {
        const { t } = useI18n()

        return t(router?.currentRoute?.value?.meta?.title)
    } catch (error) {
        console.error(error)

        return 'Aeon Trace'
    }
}

const getPageBreadcrumbs = () => {
    const router = useRouter()
    const { t } = useI18n()

    return router.currentRoute.value.meta.breadcrumbs?.map((breadcrumb: any) => {
        const route: RouteRecord | undefined = router
            .getRoutes()
            .find((r: RouteRecord) => r.name === breadcrumb || r.name === camelCase(breadcrumb))

        if (route) {
            return {
                title: t(`breadcrumbs.${route.meta.title}`),
                href: route.path,
                disabled: false,
            }
        }

        return {
            title: t(`breadcrumbs.${camelCase(breadcrumb)}`),
            href: undefined,
            disabled: true,
        }
    })
}

const displayTitle = () => {
    const router = useRouter()
    const currentMeta = router.currentRoute.value.meta

    return currentMeta.displayTitle !== false
}

export {
    getPageBreadcrumbs,
    getPageHeader,
    displayTitle,
}
