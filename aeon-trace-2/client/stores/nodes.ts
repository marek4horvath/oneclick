import { defineStore } from 'pinia'
import type { NodesState } from '@/types/nodesStore'
import type { Node } from '@/types/api/node'
import type { HydraCollection } from '@/types/api/hydra'

export const useNodesStore = defineStore('nodes', {
    state: (): NodesState => ({
        nodes: [],
    }),

    actions: {
        setNodes(nodes: Node[]) {
            this.nodes = nodes
        },

        addNode(node: Node) {
            this.nodes.push(node)
        },

        async fetchNodes(page: number = 1, itemsPerPage: number = 20) {
            const { $axios } = useNuxtApp()

            try {
                const response: { data: HydraCollection<Node[]> } = await $axios.get(
                    '/nodes',
                    {
                        params: {
                            page,
                            itemsPerPage,
                        },
                    },
                )

                this.setNodes(response.data['hydra:member'])

                return response.data['hydra:member']
            } catch (error) {
                console.error(error)
            }
        },

        async fetchNode(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response: { data: Node } = await $axios.get(`/nodes/${id}`)

                this.addNode(response.data)

                return response.data
            } catch (error) {
                console.error(error)
            }
        },

        async fetchChildNodesById(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response: { data: Node } = await $axios.get(`/nodes/${id}/getChildNodes`)

                this.addNode(response.data)

                return response.data
            } catch (error) {
                console.error(error)
            }
        },

        async fetchNodeSteps(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response: { data: Node } = await $axios.get(`/nodes/${id}/steps`)

                this.addNode(response.data)

                return response.data
            } catch (error) {
                console.error(error)
            }
        },

        async fetchNodesFromDppLogistics(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.get<Node[]>(`/node/from_dpp_logistics/${id}`)

                return response.data
            } catch (error) {
                console.error(error)
            }
        },

        async getDppsByNodeIds(nodesIds: string[], parentStepIds: string[]) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.get(`/nodes/get_dpps_by_node_ids`, {
                    params: {
                        ids: JSON.stringify(nodesIds),
                        parentStepIds: JSON.stringify(parentStepIds),
                    },
                })

                return response.data
            } catch (error) {
                console.error(error)
            }
        },

        async createNode(nodeData: Partial<Node>) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.post('/nodes', nodeData)

                this.addNode(response.data)

                return response.data
            } catch (error) {
                console.error(error)
            }
        },

        async updateNode(id: string, nodeData: Partial<Node>) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.patch(`/nodes/${id}`, nodeData, {
                    headers: { 'Content-Type': 'application/merge-patch+json' },
                })

                this.nodes = this.nodes.map(node =>
                    node.id === id ? { ...node, ...response.data } : node,
                )

                return response.data
            } catch (error) {
                console.error(error)
            }
        },

        async deleteNode(id: string) {
            const { $axios } = useNuxtApp()

            try {
                const response = await $axios.deleteRequest(`/nodes/${id}`)

                this.nodes = this.nodes.filter(node => node.id !== id)

                return response
            } catch (error) {
                console.error(error)
            }
        },
    },

    persist: {
        storage: localStorage,
    },
})
