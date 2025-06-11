import { defineStore } from 'pinia'
import type { BatchesState } from '../types/batchesStore'
import type { Batches } from '~/types/api/batches'

export const useBatchesStore = defineStore('batches', {
    state: (): BatchesState => ({
        batches: [],
    }),

    getters: {
        getBatches: (state): Batches[] => state.batches,
    },

    actions: {
        setBatches(batches: Batches[]) {
            this.batches = batches
        },

        async fetchBatches() {
            const { $axios } = useNuxtApp()
            try {
                const response = (await $axios.get('/batches'))?.data || []

                this.setBatches(response)
            } catch (error) {
                console.error(error)
            }
        },
    },
})
