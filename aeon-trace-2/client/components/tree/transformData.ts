export function transformData(data: any[]) {
    const map: any = {}
    const roots: any[] = []

    data.forEach((item: any) => {
        map[item.id] = {
            id: item.id,
            name: item.name,
            parent: item.parent?.name,
            companyName: item.company?.name ?? null,
            children: [],
        }
    })

    data.forEach((item: any) => {
        if (item.parent === undefined) {
            roots.push(map[item.id])
        } else {
            const parentItem = Object.values(map).find((node: any) => node.name === item.parent?.name)
            if (parentItem) {
                parentItem.children?.push(map[item.id])
            }
        }
    })

    return roots
}
