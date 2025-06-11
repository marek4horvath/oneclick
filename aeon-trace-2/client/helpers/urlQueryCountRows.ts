import type { RouteLocationNormalizedLoaded, Router } from 'vue-router'

const STORAGE_KEY = 'totalPerPage'
const STORAGE_PAGE_KEY = 'pages'

export function setUrlQueryCountRows(
    countRows: number,
    keyTable: string,
    router: Router,
    route: RouteLocationNormalizedLoaded,
) {
    const storedData = localStorage.getItem(STORAGE_KEY)
    const totalPerPage = storedData ? JSON.parse(storedData) : {}

    if (totalPerPage[keyTable] === countRows) {
        return
    }

    totalPerPage[keyTable] = countRows
    localStorage.setItem(STORAGE_KEY, JSON.stringify(totalPerPage))

    const currentCountRows = route.query.countRows
        ? JSON.parse(decodeURIComponent(route.query.countRows as string))
        : {}

    currentCountRows[keyTable] = countRows

    const updatedCountRows = encodeURIComponent(JSON.stringify(currentCountRows))

    router.replace({
        ...route,
        query: {
            ...route.query,
            countRows: updatedCountRows,
        },
    })
}

export function getUrlQueryCountRows(
    keyTable: string,
    route: RouteLocationNormalizedLoaded,
) {
    const currentCountRows = route.query.countRows
        ? JSON.parse(decodeURIComponent(route.query.countRows as string))
        : {}

    return currentCountRows[keyTable]
}

export function getTotalPerPage(keyTable: string): number | null {
    const storedData = localStorage.getItem(STORAGE_KEY)
    const totalPerPage = storedData ? JSON.parse(storedData) : {}

    return totalPerPage[keyTable] ?? null
}

export function setPage(keyTable: string, page: number) {
    const storedData = localStorage.getItem(STORAGE_PAGE_KEY)
    const pages = storedData ? JSON.parse(storedData) : {}

    if (pages[keyTable] === page) {
        return
    }

    pages[keyTable] = page
    localStorage.setItem(STORAGE_PAGE_KEY, JSON.stringify(pages))
}

export function getPage(keyTable: string): number | null {
    const storedData = localStorage.getItem(STORAGE_PAGE_KEY)
    const totalPerPage = storedData ? JSON.parse(storedData) : {}

    return totalPerPage[keyTable] ?? null
}
