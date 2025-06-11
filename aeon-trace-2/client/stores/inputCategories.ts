import { defineStore } from 'pinia'
import { useNuxtApp } from '#app'
import type { InputCategoryState } from '../types/inputCategoryStore'
import type { InputCategories } from '~/types/api/inputCategories'
import { getOrderParam } from "~/utils/requestFilter.ts"

export const useInputCategoriesStore = defineStore('inputCategories', {
    state: (): InputCategoryState => ({
        inputCategories: [],
        totalItems: 0,
    }),

    getters: {
        getInputCategories: (state): InputCategories[] => state.inputCategories,
        getTotalItems: (state): number => state.totalItems,
    },

    actions: {
        setInputCategories(categories: InputCategories[]) {
            this.inputCategories = categories
        },

        async fetchInputCategories(page?: number, itemsPerPage?: number, searchName?: string, orderName?: string, orderValue?: string) {
            const { $axios } = useNuxtApp()

            try {
                const params: Record<string, any> = {}

                if (page) {
                    params.page = page
                }

                if (itemsPerPage) {
                    params.itemsPerPage = itemsPerPage
                }

                if (searchName) {
                    params.name = searchName
                }

                if (orderName && orderValue) {
                    params[getOrderParam(orderName)] = orderValue
                }

                const response = (await $axios.get('/input_categories', { params }))?.data || []

                this.setInputCategories(response['hydra:member'])
                this.totalItems = response['hydra:totalItems'] || 0
            } catch (error) {
                console.info('Error fetching input categories:', error)
            }
        },

        async fetchInputCategoryById(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.get(`/input_categories/${id}`)

                return response.data
            } catch (error) {
                console.info(error)
                throw error
            }
        },

        async createInputCategory(category: InputCategories) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.post('/input_categories', category)

                this.inputCategories.push(response.data)

                return response.data
            } catch (error) {
                console.info(error)
                throw error
            }
        },

        async updateInputCategory(id: string, category: InputCategories) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.patch(`/input_categories/${id}`, category, {
                    headers: { 'Content-Type': 'application/merge-patch+json' },
                })

                const index = this.inputCategories.findIndex(cat => cat.id === id)

                if (index !== -1) {
                    this.inputCategories[index] = response.data
                }

                return response.data
            } catch (error) {
                console.info(error)
                throw error
            }
        },

        async deleteInputCategory(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.deleteRequest(`/input_categories/${id}`)

                this.inputCategories = this.inputCategories.filter(cat => cat.id !== id)

                return response
            } catch (error) {
                console.info(error)
                throw error
            }
        },
    },
})
