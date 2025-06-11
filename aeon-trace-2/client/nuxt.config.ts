import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
    title: 'Aeon Trace',

    ssr: false,

    modules: [
        '@nuxtjs/i18n',
        '@pinia/nuxt',
        'pinia-plugin-persistedstate/nuxt',
        'nuxt-echarts',
        'vuetify-nuxt-module',
        'nuxt-phosphor-icons',
        'dayjs-nuxt',
    ],

    compilerOptions: {
        allowImportingTsExtensions: true,
        typeRoots: [
            './types',
            './node_modules/@types',
        ],
        types: [
            'node',
            '@nuxt/types',
            '@vue/runtime-core',
            'vue',
        ],
    },

    routeRules: {
        '/': { redirect: '/dashboard' },
    },

    plugins: [
        'plugins/pinia.ts',
        'plugins/datetime.ts',
        'plugins/context-menu.ts',
    ],

    css: [
        'assets/style/index.scss',
    ],

    vite: {
        assetsInclude: ['**/*.html'],
        css: {
            preprocessorOptions: {
                scss: {
                    api: 'modern-compiler',
                },
            },
        },

        server: {
            allowedHosts: ['productdigitalpass.fbi.com'],
        },
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

    pinia: {
        autoImports: [
            'defineStore',
            ['storeToRefs', 'defineStore'],
        ],
    },

    imports: {
        dirs: ['stores', 'dialogs', 'components', 'utils'],
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

    compatibilityDate: '2025-02-17',
})
