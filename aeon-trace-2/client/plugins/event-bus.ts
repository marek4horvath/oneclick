import mitt from 'mitt'

export default defineNuxtPlugin(() => {
    const eventBus = mitt()

    return {
        provide: {
            event: eventBus.emit,
            listen: eventBus.on,
        },
    }
})
