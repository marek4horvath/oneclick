<template>
    <NuxtLayout>
        <table-data v-model:search="search" :items="productTable" @set:rowData="getRowData">
            <template #table-title>
                <v-text-field 
                    v-model="search" 
                    density="compact" 
                    :label="$t('search')" 
                    prepend-inner-icon="mdi-magnify"
                    variant="solo-filled" 
                    flat 
                    hide-details 
                    class="mx-0"
                    single-line>
                </v-text-field>

                <v-spacer></v-spacer>

                <v-btn class="me-2" color="primary" @click="openDialog('addProduct')">{{ $t('products.addProduct') }}</v-btn>
                <v-btn class="me-2" color="primary" @click="openDialog('linkProduct')">{{ $t('products.linkProduct') }}</v-btn>
                <v-btn class="me-2" color="primary" @click="openDialog('createInputCategory')">{{ $t('products.createInputCategoty') }}</v-btn>
            </template>

            <template #table-actions="{ item }">
                <PhChartBarHorizontal 
                    :size="20" 
                    color="#1967c0"
                    weight="light"  
                    @click="detailProduct(item)"
                    class="cursor-pointer me-2"
                />
                <PhPencilSimpleLine 
                    :size="20" 
                    color="#1967c0" 
                    weight="light" 
                    @click="editProduct(item)"
                    class="cursor-pointer me-2"
                />
                <PhTrash 
                    :size="20" 
                    color="#1967c0" 
                    weight="light" 
                    @click="deleteProduct(item)"
                    class="cursor-pointer me-2"
                />
            </template>

            <template #table-image="{ item }">
                <v-img
                    :src="item.image"
                    height="64"
                    cover
                    class="circle-img"
                ></v-img>
            </template>
        </table-data>

        <Popup 
            v-model="dialogVisible" 
            :title="dialogTitle" 
            :content="dialogContent"
        >
            <template #popup-content>
                <div v-if="dialogType === 'linkProduct'">
                    <v-form ref="form" @keyup.native.enter="linkProduct">
                        <v-container>
                            <v-row no-gutters>
                                <v-col>
                                    <v-select
                                        label="Selected company"
                                        :items="selectCompany"
                                        v-model="selectedCompany"
                                    ></v-select>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col>
                                    <v-select
                                        :label="$t('products.products')"
                                        :items="selectProduct"
                                        v-model="selectedProduct"
                                        ></v-select>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>
                </div>
            </template>

            <template #popup-actions>
                <div v-if="dialogType === 'linkProduct'">
                    <v-btn color="primary" variant="tonal" text @click="linkProduct">{{ $t('products.linkProduct') }}</v-btn>
                </div>
            </template>
        </Popup>
    </NuxtLayout>
</template>

<script setup lang="ts">

import type { ProductPayload } from '~/interface/product';
import type { CompanyPayload } from '~/interface/company';
import type { TableData } from '~/interface/tableData';
import { PhChartBarHorizontal, PhTrash, PhPencilSimpleLine } from "@phosphor-icons/vue";
import { useI18n } from 'vue-i18n'

const productStore = useProductStore()
const companyStore = useCompanyStore()
const router = useRouter()
const { $toast } = useNuxtApp()
const { t } = useI18n()
const search: String = ref('')

const products: ProductPayload[] = computed(() => productStore.products)
const companies: CompanyPayload[] = computed(() => companyStore.companies)
const productTable:TableData = ref({
    headers: [],
    data: [],
});
const dialogVisible = ref(false);
const dialogTitle = ref('');
const dialogContent = ref('');
const dialogType = ref('');

const selectProduct = ref([]);
const selectedProduct = ref(null);
const selectCompany = ref([])
const selectedCompany = ref(null);

definePageMeta({
    title: 'Products',
    name: 'products',
    layout: 'dashboard',
    middleware: 'auth'
});

const openDialog = (type: string) => {
    dialogVisible.value = true;
    
    switch (type) {
        case 'addProduct':
                dialogVisible.value = false
                router.push('/product/add-product')
            break;

        case 'linkProduct':
            dialogTitle.value = 'Link Product';
            dialogType.value = type
    
            selectProduct.value = products.value.map((product: any) => ({
                title: product.name,
                value: product.id,
            }));

            selectCompany.value = companies.value.map((company: any) => ({
                title: company.name,
                value: company.id,
            }));
            
            break;

        case 'createInputCategory':
            dialogTitle.value = 'Create Input Category';
            dialogContent.value = 'Form to create a new input category goes here.';
            dialogType.value = type
            break;

        default:
            dialogTitle.value = 'Unknown Action';
            dialogContent.value = 'No content available for this action.';
    }
};

const updateProduct = () => {
    productTable.value.headers = [
        { key: 'image', title: ''},
        { key: 'name', title: t('productTableHeader.productName') },
        { key: 'company', title: t('productTableHeader.company') },
        { key: 'steps', title: t('productTableHeader.numberOfStep') },
        { key: 'inputs', title: t('productTableHeader.numberOfInput') },
        { key: 'actions', title: t('productTableHeader.actions') },
    ];
    
    productTable.value.data = products.value.map((product: any) => {
        const steps = Array.isArray(product?.stepsTemplate?.steps)
            ? product?.stepsTemplate?.steps
            : product?.stepsTemplate?.steps ? [product?.stepsTemplate?.steps] : [];

        return {
            ...product,
            company: product.companies
                ?.map((company: any) => company.name)
                .join(', ') || '----',
            steps: steps.length || 0,
            inputs: steps.reduce((acc: number, step: any) => {
                return acc + (step && step.inputs ? step.inputs.length : 0);
            }, 0) || 0,
            image: product.productImage
        };
    });
}

const editProduct = (data: any) => {
    router.push(`/product/edit-product/${data.id}`)
}

const getRowData = (rowData: any) => {    
    router.push(`product/detail/${rowData.id}`)   
}

const detailProduct = (data: any) => {
    router.push(`/product/detail/${data.id}`)   
}

const deleteProduct = (data: any) => {
    productStore.deleteProduct(data.id)
    updateProduct()
    $toast.success(t('messages.deleteProduct'));
}

const linkProduct = () =>{
    productStore.linkProductToCompany(selectedProduct.value, selectedCompany.value);

    dialogVisible.value = false
}

const createInputCategory = () =>{
    dialogVisible.value = false
}

onMounted(() => {
    // productStore.resetToDefault()
    updateProduct()
    console.log(productTable);
    
})

</script>
