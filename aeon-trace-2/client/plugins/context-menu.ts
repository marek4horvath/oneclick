import '@imengyu/vue3-context-menu/lib/vue3-context-menu.css'
import { ContextMenu } from '@imengyu/vue3-context-menu'

export default defineNuxtPlugin(nuxtApp => {
    if (process.client) {
        nuxtApp.vueApp.use(ContextMenu)
    }
})
