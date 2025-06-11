import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

export default defineNuxtPlugin(nuxtApp => {
    if (process.client) {
        nuxtApp.provide('VueDatePicker', VueDatePicker)
    }
})
