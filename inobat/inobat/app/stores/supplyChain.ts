import type { SupplyChainPayload } from "~/interface/supplyChain";
import type { NodePayload } from "~/interface/node";

export const useSupplyChainStore = defineStore('supplyChains', {
    state: () => ({
        supplyChains: [{
            "@context": "/api/contexts/SupplyChainTemplate",
            "@id": "/api/supply_chain_templates/66fb3085",
            "@type": "SupplyChainTemplate",
            id: "66fb3085",
            name: "Default supply chain",
            nodes: []
        }] as SupplyChainPayload[],
    }),

    getters: {
        getSupplyChains: ({ supplyChains }: { supplyChains: SupplyChainPayload[] }) => supplyChains,

        getsupplyChainById: ({ supplyChains }: { supplyChains: SupplyChainPayload[] }) => (id: string) => {
            return supplyChains.find(supplyChain => supplyChain.id === id)
        },

        getNode: ({ supplyChains }: { supplyChains: SupplyChainPayload[] }) => (supplyChainId: string, nodeId: string): NodePayload | undefined => {
            const supplyChain = supplyChains.find(supplyChain => supplyChain.id === supplyChainId);
        
            if (supplyChain) {
                return supplyChain.nodes.find(node => node.id === nodeId);
            }
        
            return undefined;
        },
        
    },

    actions: {
        addSupplyChains(payload: SupplyChainPayload) {
            this.supplyChains.push(payload);
        },

        deleteSupplyChains(supplyChainId: string) {
            this.supplyChains = this.supplyChains.filter(supplyChain => supplyChain.id !== supplyChainId);
        },

        editSupplyChains(supplyChainId: string, updatedSupplyChain: Partial<SupplyChainPayload>) {
            const index = this.supplyChains.findIndex(supplyChain => supplyChain.id === supplyChainId);

            if (index !== -1) {
                this.supplyChains[index] = { ...this.supplyChains[index], ...updatedSupplyChain };
            }
        },

        addNode(supplyChainId: string, node: NodePayload) {
            const supplyChain = this.supplyChains.find(supplyChain => supplyChain.id === supplyChainId);

            if (supplyChain) {
                supplyChain.nodes.push(node);
            }
        },

        editNode(supplyChainId: string, nodeId: string, updatedNode: Partial<NodePayload>) {
            const supplyChain = this.supplyChains.find(supplyChain => supplyChain.id === supplyChainId);

            if (!supplyChain) {
                return
            }

            const nodeIndex = supplyChain.nodes.findIndex(node => node.id === nodeId);

            if (nodeIndex !== -1) {
                supplyChain.nodes[nodeIndex] = { ...supplyChain.nodes[nodeIndex], ...updatedNode };
            }
            
        },

        deleteNode(supplyChainId: string, nodeId: string) {
            const supplyChain = this.supplyChains.find(supplyChain => supplyChain.id === supplyChainId);
        
            if (!supplyChain) {
                return
            }

            supplyChain.nodes = supplyChain.nodes.filter(node => node.id !== nodeId);

            supplyChain.nodes.forEach((node) => {
                if (node.childrens) {
                    node.childrens = node.childrens.filter(child => child.id !== nodeId);
                    this.editNode(supplyChainId, node.id, node);
                }
            });
    
            supplyChain.nodes.forEach((node) => {
                if (node.parents) {
                    node.parents = node.parents.filter(parent => parent.id !== nodeId);
                    this.editNode(supplyChainId, node.id, node);
                }
            });

        }        
        
    },

    persist: true,
});
