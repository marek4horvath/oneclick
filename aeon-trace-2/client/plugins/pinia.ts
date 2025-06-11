import { createPersistedState } from 'pinia-plugin-persistedstate'

export default defineNuxtPlugin(({ $pinia }) => {
    if (process.client) {
        $pinia.use(createPersistedState())
    }
})
