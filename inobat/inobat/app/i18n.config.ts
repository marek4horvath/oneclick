import en from './locales/en'

export default defineI18nConfig(() => {
    return {
        legacy: false,
        locale: 'en',
        messages: {
            en,
        },
    }
})
