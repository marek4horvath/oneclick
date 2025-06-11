import type { AuthPayload } from "~/interface/auth"

export const useAuthStore = defineStore('auth', {
    state: () => ({
        email: '',
    }),

    getters: {
        isLoggedIn: (state) => !!state.email,
    },

    actions: {
        login(payload: AuthPayload) {
            this.email = payload.email
        },

        logout() {
            this.email = ''
        },
    },

    persist: true,
})
