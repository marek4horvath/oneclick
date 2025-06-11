import en from './locales/en/index'

export default defineI18nConfig(() => {
    return {
        legacy: false,
        locale: 'en',
        messages: {
            en,
        },
    }
})
