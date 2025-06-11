export default defineNuxtConfig({
    title: 'InoBat',

    modules: [
        'vuetify-nuxt-module',
        '@nuxtjs/i18n',
        '@pinia/nuxt',
        'pinia-plugin-persistedstate/nuxt',
        'nuxt-echarts'
    ],

    plugins: [
        '~/plugins/vue3-toastify.ts',
    ],

    css: [
        'assets/css/index.scss',
    ],

    vite: {
        css: {
            preprocessorOptions: {
                scss: {
                    api: 'modern-compiler',
                }
            }
        }
    },

    i18n: {
        locales: [{
            code: 'en',
            name: 'English',
        }],
        defaultLocale: 'en',
        strategy: 'no_prefix',
        vueI18n: './i18n.config.ts',
    },

    vuetify: {
        moduleOptions: {
            /* module specific options */
        },
        vuetifyOptions: {
            /* vuetify options */
        }
    },

    pinia: {
        storesDirs: ['./stores'],
    },

    echarts: {
        charts: ['BarChart'],
        components: ['DatasetComponent', 'GridComponent', 'TooltipComponent'],
    },

    devtools: {
        enabled: true,

        timeline: {
            enabled: true,
        },
    },
})
