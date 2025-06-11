<template>
    <NuxtLayout>
        <div class="detail-product">
            <accordion-table :headers="stepsHeaders" :items="steps">
                <template #custom-coll="{ item, header }">
                    {{ item[header.key] ?? '----'}}
                </template>
    
                <template #custom-actions="{ item }">
                    <PhPencilSimpleLine
                        :size="20" 
                        color="#1967c0"
                        weight="light"  
                        @click="openDialog('editSteps', item)"
                        class="cursor-pointer me-2"
                    />

                    <PhListPlus
                        :size="20" 
                        color="#1967c0"
                        weight="light"  
                        @click="openDialog('addInput', item)"
                        class="cursor-pointer me-2"
                    />

                    <PhHand 
                        :size="20" 
                        color="#1967c0"
                        weight="light"  
                        class="cursor-pointer me-2"
                    />

                    <PhTrash
                        :size="20" 
                        color="#1967c0"
                        weight="light"  
                        @click="deleteStep(item)"
                        class="cursor-pointer me-2"
                    />
                </template>

                <template #accordion-content="{ item }">
                    <accordion-table :headers="headersInputs" :items="item.subItems" :fixed-width="true">

                        <template #custom-coll="{ item, header }">
                            <div v-if="header.key === 'type'">
                                <div class="d-flex align-center">
                                    <icon-type-inputs class="me-2" :type="item[header.key]"/>
                                    {{ item[header.key] ?? '----' }}
                                </div>
                            </div>
                            <div v-else>
                                {{ item[header.key] ?? '----'}}
                            </div>

                        
                        </template>

                        <template #custom-actions="{ item }">
                            <PhPencilSimpleLine
                                :size="20" 
                                color="#1967c0"
                                weight="light"  
                                @click="openDialog('editInput', item)"
                                class="cursor-pointer me-2"
                            />

                            <PhHand 
                                :size="20" 
                                color="#1967c0"
                                weight="light"  
                                class="cursor-pointer me-2"
                            />

                            <PhTrash
                                :size="20" 
                                color="#1967c0"
                                weight="light"  
                                @click="deleteInput(item)"
                                class="cursor-pointer me-2"
                            />
                        </template>

                    </accordion-table>
                </template>
                <template #accordion-footer>
                    <div class="accordion-footer">
                        <v-btn class="me-2" color="primary" @click="openDialog('addSteps')">{{ $t('products.addSteps') }}</v-btn>
                    </div>
                </template>
                
            </accordion-table>
        </div>

        <Popup 
            v-model="dialogVisible" 
            :title="dialogTitle" 
            :content="dialogContent"
        >
            <template #popup-content>
                <div v-if="dialogType === 'addSteps' || dialogType === 'editSteps'">
                    <v-form  ref="form" v-model="valid" @keyup.native.enter="dialogType === 'addSteps' ? addSteps : editSteps">
                        <v-container>

                            <v-row >
                                <v-col>
                                    <v-text-field 
                                        v-model="formStep.name" 
                                        :label="$t('products.stepsName')" 
                                        type="text" 
                                        required
                                        :rules="nameRules"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            
                            <v-row >
                                <v-col>
                                    <v-select
                                        :label="$t('products.selectProcess')" 
                                        :items="process"
                                        v-model="formStep.process"
                                    ></v-select>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col>
                                    <v-select
                                        :label="$t('products.selectBatchs')"
                                        :items="batchs"
                                        v-model="formStep.batchs"
                                        ></v-select>
                                </v-col>
                            </v-row>

                            <v-row >
                                <v-col>
                                    <v-text-field 
                                        v-model="formStep.quantity" 
                                        :label="$t('products.stepQuantity')" 
                                        type="text" 
                                        required
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col>
                                    <v-select
                                        :label="$t('products.selectParent')"
                                        :items="parent"
                                        v-model="formStep.parentId"
                                    ></v-select>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>
                </div>

                <div v-if="dialogType === 'addInput' || dialogType === 'editInput'">
                    <v-form  ref="form" v-model="valid" @keyup.native.enter="dialogType === 'addInput' ? addInput : editInput">
                        <v-container>

                            <v-row >
                                <v-col>
                                    <v-text-field 
                                        v-model="formInput.name" 
                                        :label="$t('products.stepsName')" 
                                        type="text" 
                                        required
                                        :rules="nameRules"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            
                            <v-row >
                                <v-col>
                                    <v-select
                                        :label="$t('products.selectType')" 
                                        :items="inputType"
                                        v-model="formInput.type"
                                        required
                                        :rules="typeRules"
                                    ></v-select>
                                </v-col>
                            </v-row>

                        </v-container>
                    </v-form>
                </div>
            </template>
            <template #popup-actions>
                <div v-if="dialogType === 'addSteps'">
                    <v-btn color="primary" variant="tonal"  text @click="addSteps">{{ $t('products.addSteps') }}</v-btn>
                </div>

                <div v-if="dialogType === 'editSteps'">
                    <v-btn color="primary" variant="tonal"  text @click="editSteps">{{ $t('products.editSteps') }}</v-btn>
                </div>

                <div v-if="dialogType === 'addInput'">
                    <v-btn color="primary" variant="tonal" text @click="addInputs">{{ $t('products.addInputs') }}</v-btn>
                </div>

                <div v-if="dialogType === 'editInput'">
                    <v-btn color="primary" variant="tonal" text @click="editInputs">{{ $t('products.editInputs') }}</v-btn>
                </div>
            </template>
        </Popup>
    </NuxtLayout>
</template>

<script setup lang="ts">

import { PhHand, PhTrash, PhPencilSimpleLine, PhListPlus } from "@phosphor-icons/vue";
import type { ProductPayload } from '~/interface/product';
import {PROCESS} from '~/types/process';
import {BATCHS} from '~/types/batchs';
import { useI18n } from 'vue-i18n'

const productStore = useProductStore()
const { $toast } = useNuxtApp()
const { t } = useI18n()
const route = useRoute()
const url = route.path
const productId = url.split('/').pop()
const product: ProductPayload[] = computed(() => productId ? productStore.getProductById(productId) : [])
const form = ref(null)
const valid = ref(false)
const formStep = ref({
    id: '',
    name: '',
    process: '',
    batchs: '',
    quantity: 0,
    parent: [],
    parentId: '',
    inputs: [],
    sort: 0
})
const formInput = ref({
    id: '',
    name: '',
    type: '',
    sort: 0
})
const process = ref(PROCESS)
const batchs = ref(BATCHS)
const inputType = ref([
    { title: t('inputType.text'), value: 'text' },
    { title: t('inputType.textArea'), value: 'textarea' },
    { title: t('inputType.selectImage'), value: 'image' },
    { title: t('inputType.selectImages'), value: 'images' },
    { title: t('inputType.selectFile'), value: 'file' },
    { title: t('inputType.selectNumerical'), value: 'numerical' },
    { title: t('inputType.selectCoordinates'), value: 'coordinates' },
    { title: t('inputType.selectDataTime'), value: 'dateTime' },
])

const parent = ref([])

const stepsHeaders = [
    { title: t('stepTableHeader.name'), key: 'name' },
    { title: t('stepTableHeader.previousStep'), key: 'previousStep' },
    { title: t('stepTableHeader.nextStep'), key: 'nextStep' },
    { title: t('stepTableHeader.quantity'), key: 'quantity' },
    { title: t('stepTableHeader.process'), key: 'process' },
    { title: t('stepTableHeader.batch'), key: 'batchs' },
    { title: t('stepTableHeader.numberInput'), key: 'numberInputs' },
    { title: t('stepTableHeader.actions'), key: 'actions' },
];

const headersInputs = [
    { title: t('inputTableHeader.name'), key: 'name' },
    { title: t('inputTableHeader.type'), key: 'type' },
    { title: t('inputTableHeader.actions'), key: 'actions' },
];

const steps = ref([])

const dialogVisible = ref(false);
const dialogTitle = ref('');
const dialogContent = ref('');
const dialogType = ref('');

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
];

const typeRules = [
    (v: string) => !!v || 'Input type is required',
    (v: string) => v.trim().length > 0 || 'Input type cannot be empty',
]

definePageMeta({
    title: 'Product detail',
    name: 'product-detail[...id]',
    layout: 'dashboard',
    middleware: 'auth'
});

const openDialog = (type: string, data?: any) => {
    dialogVisible.value = true;
    parent.value = []
    product.value?.stepsTemplate?.steps.forEach((step: any) => {
        parent.value.push({
            value: step.id,
            title: step.name,
        })
    })

    switch (type) {
        case 'addSteps':
            dialogTitle.value = 'Add steps';
            dialogType.value = type

            break;

        case 'editSteps':
            dialogTitle.value = 'Edit steps';
            dialogType.value = type
            formStep.value = data
            
            break;

        case 'addInput':
            dialogTitle.value = 'Add input';
            dialogType.value = type
            formStep.value = data
            
            break;
        
        case 'editInput':            
            dialogTitle.value = 'Edit input';
            dialogType.value = type
            formInput.value = data
                
            break;
        default:
            dialogTitle.value = 'Unknown Action';
            dialogContent.value = 'No content available for this action.';
    }
};

const resetForm = () => {
    formStep.value = {
        id: '',
        name: '',
        process: '',
        batchs: '',
        quantity: 0,
        parent: [],
        parentId: '',
        inputs: []
    }

    formInput.value = {
        id: '',
        name: '',
        type: '',
        sort: 0
    }
}

const editSteps = () => {
    if(productId) {
        const formValidation: any = form.value
        formValidation.validate()

        if (!valid.value) {
            return
        }

        productStore.editStep(productId, formStep.value.id, formStep.value)
        updateSteps()
        dialogVisible.value = false

        $toast.success(t('messages.editStep'));
    }
};

const editInputs = () => {
    if(productId) {
        const formValidation: any = form.value
        formValidation.validate()

        if (!valid.value) {
            return
        }

        productStore.editInput(productId, formInput.value.stepId, formInput.value.id, formInput.value)
        updateSteps()
        dialogVisible.value = false

        $toast.success(t('messages.editInput'));
    }
}

const deleteStep = (step: any) => {
    if(productId) {
        productStore.deleteStep(productId, step.id)
        updateSteps()

        $toast.success(t('messages.deleteStep'));
    }
};

const deleteInput = (input: any) => {
    if(productId) {  
        productStore.deleteInput(productId, input.stepId, input.id)
        updateSteps()

        $toast.success(t('messages.deleteInput'));
    }
}

const addSteps = () => {
    if(productId) {
        const formValidation: any = form.value
        formValidation.validate()

        if (!valid.value) {
            return
        }

        if ( !product.value?.stepsTemplate) {
            product.value.stepsTemplate = {
                id: Math.random().toString(16).substr(2, 8),
                name: `${product.value.name} - SupplyChaine`,
                steps: []
                
            };
        }

        if (Array.isArray(product.value.stepsTemplate.steps)) {
            formStep.value.id = Math.random().toString(16).substr(2, 8)
            const stepParent = product.value.stepsTemplate.steps.find(step => step.id === formStep.value.parentId) || null;

            if (stepParent) {
                formStep.value.parent.push(stepParent);
            } else {
                formStep.value.parent = null
            }

            formStep.value.sort = product.value.stepsTemplate.steps?.length + 1

            product.value.stepsTemplate.steps.push(formStep.value);
        }

        productStore.editProduct(productId, product.value)
        resetForm()
        updateSteps()
        dialogVisible.value = false

        $toast.success(t('messages.createdStep'));
    }

};

const addInputs = () => {
    if(productId) {
        const formValidation: any = form.value
        formValidation.validate()

        if (!valid.value) {
            return
        }

        formInput.value.id = Math.random().toString(16).substr(2, 8),
        formInput.value.sort = formStep.value.inputs?.length + 1 || 0,
        formInput.value.stepId = formStep.value.id
        formStep.value.inputs.push(formInput.value)

        productStore.editStep(productId, formStep.value.id, formStep.value)
        resetForm()
        dialogVisible.value = false

        $toast.success(t('messages.createdInput'));
    }
    
}

const getParentStep = (parents: any[]): string => {
    if (!Array.isArray(parents) || parents.length === 0) {
        return "";
    }

    const currentNames = parents.map((parent) => parent.name || "")
    
    return currentNames[0]
}

const nextSteps = (steps: any) => {
    steps.forEach((step: any) => {
        if (step.parent && step.parent.length > 0) {
            step.parent.forEach((child: any) => {
                const stepByParent = steps.filter((step: any) => step.id === child.id);
                if (stepByParent.length > 0) {
                    stepByParent[0].nextStep = stepByParent[0].nextStep
                        ? `${stepByParent[0].nextStep}, ${step.name}`
                        : step.name;
                }
            })
            nextSteps(step.parent)
        } else {
            if(step.nextStep) {
                step.nextStep = '----'
            }
            
        }
    })
}

const updateSteps = () => {
    if(!product.value?.stepsTemplate?.steps) {
        return
    }
    steps.value = product.value.stepsTemplate.steps.map((step: any) => {
        return {
            ...step,
            id: step.id ? step.id : Math.random().toString(16).substr(2, 8),
            numberInputs: step.inputs?.length || 0,
            subItems: step?.inputs || [],
            previousStep: step.parent ? getParentStep(step.parent) : '----',
        };
    });
    nextSteps(steps.value)
};

onMounted(() => {
    updateSteps()
})
</script>

<style lang="scss" scoped>
.detail-product {
    width: 100%;
}
</style>
