<template>
    <NuxtLayout>
        <div class="w-100">
            <div class="detail-supply-chain">
                <accordion-table :headers="nodesHeaders" :items="nodes">
                    <template #custom-coll="{ item, header }">
                        {{ item[header.key] ?? '----'}}
                    </template>
        
                    <template #custom-actions="{ item }">
                        <PhPencilSimpleLine
                            :size="20" 
                            color="#1967c0"
                            weight="light"  
                            @click="openDialog('editNodes', item)"
                            class="cursor-pointer me-2"
                        />

                        <PhTrash
                            :size="20" 
                            color="#1967c0"
                            weight="light"  
                            @click="deleteNode(item)"
                            class="cursor-pointer me-2"
                        />
                    </template>

                    <template #accordion-footer>
                        <div class="accordion-footer">
                            <v-btn class="me-2" color="primary" @click="openDialog('addNodes')">{{ $t('supply.addNode') }}</v-btn>
                        </div>
                    </template>
                    
                </accordion-table>
            </div>

            <div>
                <TreeFlow
                    class="tree-dpp"
                    :data="nodes"
                    :is-click-node="false"
                    id-popup="tree-popup"
                    @node-data="(data) => { console.log(data) }"
                />
            </div>
        </div>

        <Popup 
            v-model="dialogVisible" 
            :title="dialogTitle" 
            :content="dialogContent"
        >
            <template #popup-content>
                <div v-if="dialogType === 'addNodes' || dialogType === 'editNodes'">
                    <v-form  ref="form" v-model="valid">
                        <v-container>
                            <v-row >
                                <v-col>
                                    <v-text-field 
                                        v-model="formNode.name"
                                        :label="$t('supply.name')" 
                                        type="text" 
                                        required
                                        :rules="nameRules"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row >
                                <v-col>
                                    <v-textarea
                                        v-model="formNode.description"
                                        :label="$t('supply.description')" 
                                        type="text" 
                                        required
                                    ></v-textarea>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col>
                                    <v-select
                                        v-model="parents"
                                        :label="$t('supply.selectParents')"
                                        :items="selectParents"
                                        multiple
                                        ></v-select>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>
                </div>
            </template>

            <template #popup-actions>
                <div v-if="dialogType === 'addNodes'">
                    <v-btn color="primary" variant="tonal"  text @click="addNodes">{{ $t('supply.addNode') }}</v-btn>
                </div>
                <div v-if="dialogType === 'editNodes'">
                    <v-btn color="primary" variant="tonal"  text @click="editNodes">{{ $t('supply.addNode') }}</v-btn>
                </div>
            </template>

        </Popup>
    </NuxtLayout>
</template>

<script setup lang="ts">

import { PhTrash, PhPencilSimpleLine } from "@phosphor-icons/vue";
import type { SupplyChainPayload } from '~/interface/supplyChain';
import { useI18n } from 'vue-i18n'

const supplyChainStore = useSupplyChainStore()
const { $toast } = useNuxtApp()
const { t } = useI18n()
const route = useRoute()
const url = route.path
const supplyId = url.split('/').pop()
const supplyChain: SupplyChainPayload[] = computed(() => supplyId ? supplyChainStore.getsupplyChainById(supplyId) : [])
const nodesHeaders = [
    { title: t('supplyTableHeader.name'), key: 'name' },
    { title: t('supplyTableHeader.previousNode'), key: 'previousNode' },
    { title: t('supplyTableHeader.nextNode'), key: 'nextNode' },
    { title: t('supplyTableHeader.action'), key: 'actions' },
];

const selectParents = ref([])
const parents = ref([])
const nodes = ref([])

const form = ref(null)
const valid = ref(false)

const formNode = ref({
    id: '',
    name: '',
    description: '',
    parents: [],
    childrens: [],
    sort: 0
})

const dialogVisible = ref(false);
const dialogTitle = ref('');
const dialogContent = ref('');
const dialogType = ref('');

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
];

definePageMeta({
    title: 'supply-chain detail',
    name: 'detail[...id]',
    layout: 'dashboard',
    middleware: 'auth'
});

const openDialog = (type: string, data?: any) => {
    dialogVisible.value = true;
    selectParents.value = []
    supplyChain.value?.nodes.forEach((node: any) => {
        selectParents.value.push({
            value: node.id,
            title: node.name,
        })
    })

    switch (type) {
        case 'addNodes':
            dialogTitle.value = 'Add nodes';
            dialogType.value = type

            break;

        case 'editNodes':
            dialogTitle.value = 'Edit nodes';
            dialogType.value = type
            formNode.value = data

            data?.parents.forEach((parent: any) => {
                parents.value.push(parent.id)
            })

            selectParents.value = selectParents.value.filter((parent: { value: string }) => parent.value !== data.id);
            
            break;
        default:
            dialogTitle.value = 'Unknown Action';
            dialogContent.value = 'No content available for this action.';
    }
};

const updateNodes = () => {
    nodes.value = supplyChain.value.nodes.map((node: any) => {    
        const parentNames = node.parents.map((parent: any) => parent.name).join(', ');
        const childrenNames = node.childrens?.map((children: any) => children.name).join(', ') || '---';

        return {
            ...node,
            previousNode: parentNames || '----',
            nextNode: childrenNames || '----'
        };

    });
}

const resetForm = () => {
    formNode.value = {
        id: '',
        name: '',
        description: '',
        parents: [],
        childrens: [],
        sort: 0
    }

    parents.value = []
    selectParents.value = []
}

const addNodes = () => {
    if(!supplyId) {
        return
    }

    const formValidation: any = form.value
    formValidation.validate()

    if (!valid.value) {
        return
    }

    let nodeParent: any[] = []
    formNode.value.id = Math.random().toString(16).substr(2, 8)
    formNode.value.sort = supplyChain.value.nodes?.length + 1

    if(parents.value) {
        nodeParent = parents.value.map((parent: any) => {
            const node = supplyChainStore.getNode(supplyId, parent)
            node.childrens.push(formNode.value)

            supplyChainStore.editNode(supplyId, parent, node)
            return node
        })
    }

    formNode.value.parents = nodeParent
    
    supplyChainStore.addNode(supplyId, formNode.value)
    resetForm()
    updateNodes()
    dialogVisible.value = false;

    $toast.success(t('messages.createdNode'));
}

const editNodes = () => {
    if (supplyId) {
        const formValidation: any = form.value
        formValidation.validate()

        if (!valid.value) {
            return
        }

        formNode.value = (({ previousNode, nextNode, ...rest }) => rest)(formNode.value);

        const updatedParents = parents.value.map((parentId: string) => {
            const existingParent = formNode.value.parents.find((parent: any) => parent.id === parentId);
            const node = supplyChainStore.getNode(supplyId, parentId);

            if (existingParent) {
                
                node.childrens = node.childrens.map((children: any) => {
                    if (formNode.value.id === children.id) {
                        return formNode.value;
                    }
                    return children;
                });

                supplyChainStore.editNode(supplyId, parentId, node);
                return existingParent;
            } else {
                node.childrens.push(formNode.value);
                supplyChainStore.editNode(supplyId, parentId, node);
                return node;
            }
        });

        formNode.value.parents.forEach((oldParent: any) => {
            if (!parents.value.includes(oldParent.id)) {
                const node = supplyChainStore.getNode(supplyId, oldParent.id);
                node.childrens = node.childrens.filter((child: any) => child.id !== formNode.value.id);

                supplyChainStore.editNode(supplyId, oldParent.id, node);
            }
        });

        formNode.value.childrens = formNode.value.childrens.map((child: any) => {
            const existingChild = supplyChainStore.getNode(supplyId, child.id);

            if (existingChild) {
                existingChild.parents = existingChild.parents.map((parent: any) => {
                    if (parent.id === formNode.value.id) {
                        return { ...formNode.value };
                    }
                    return parent;
                });

                supplyChainStore.editNode(supplyId, existingChild.id, existingChild);
                return existingChild;
            } else {
                console.warn(`Child with ID ${child.id} not found`);
                return null;
            }
        }).filter(Boolean);

        formNode.value.parents = updatedParents;

        supplyChainStore.editNode(supplyId, formNode.value.id, formNode.value);
        resetForm();
        updateNodes();
        dialogVisible.value = false;

        $toast.success(t('messages.editNode'));
    }
};

const deleteNode = (node: any) => {
    if(supplyId) {
        supplyChainStore.deleteNode(supplyId, node.id)
        updateNodes()

        $toast.success(t('messages.deleteNode'));
    }
}

onMounted(() => {
    updateNodes()
})

const nodeData = async (node: any) => {
    console.log(node)
}
</script>

<style lang="scss" scoped>
.detail-supply-chain {
    width: 100%;
}
</style>
