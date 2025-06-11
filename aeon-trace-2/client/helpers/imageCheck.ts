export function checkImage(url: string): Promise<boolean> {
    return new Promise((resolve: any) => {
        const { $axios } = useNuxtApp()

        $axios.get(url).then(() => {
            resolve(true)
        }).catch(() => {
            resolve(false)
        })
    })
}
