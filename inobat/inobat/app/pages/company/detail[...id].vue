<template>
    <NuxtLayout>
        <div class="detail-company">
            <v-container>
                <v-card color="primary" variant="tonal" class="mx-auto">
                    <v-card-item>
                        <v-row class="company-info">

                            <v-col cols="auto" class="logo-column">
                                <v-img :src="companyData?.companyLogo" height="100" width="100" contain></v-img>
                            </v-col>

                            <v-col cols="auto" class="d-flex flex-column align-center justify-center">
                                <div>
                                    <p>
                                        <span class="company-name">{{ $t('companies.companyName') }}: {{ companyData?.name }}</span>
                                    </p>
                                    <p>
                                        <span class="company-address">{{ $t('address.title') }}: {{ formattedAddress }}</span>
                                    </p>
                                </div>
                            </v-col>
                        </v-row>
                    </v-card-item>
                </v-card>
            </v-container>

            <table-data v-model:search="search" :items="productTable" @set:rowData="getRowData">
                <template #table-title>
                    {{ $t('products.products') }}
                    <v-spacer></v-spacer>

                    <v-text-field 
                        v-model="search" 
                        density="compact" 
                        :label="$t('search')" 
                        prepend-inner-icon="mdi-magnify"
                        variant="solo-filled" 
                        flat 
                        hide-details 
                        class="me-2"
                        single-line>
                    </v-text-field>

                    <v-btn class="me-2" color="primary" @click="addProduct">{{ $t('products.addProduct') }}</v-btn>
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
        </div>
    </NuxtLayout>
</template>

<script setup lang="ts">

import type { ProductPayload } from '~/interface/product';
import type { TableData } from '~/interface/tableData';
import { PhChartBarHorizontal, PhTrash, PhPencilSimpleLine } from "@phosphor-icons/vue";
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const companyStore = useCompanyStore()

const productStore = useProductStore()
const search: String = ref('')

const products: ProductPayload[] = computed(() => productStore.products)
const productTable:TableData = ref({
    headers: [],
    data: [],
});

const url = route.path
const companyId = url.split('/').pop()

definePageMeta({
    title: `Company detail`,
    name: 'company-detail[...id]',
    layout: 'dashboard',
    middleware: 'auth'
});

const companyData = computed(() => {
    return companyStore.getCompanyById(companyId);
});

const formattedAddress = computed(() => {
    if (!companyData.value) {
        return '';
    }

    return `${companyData.value.street}, ${companyData.value.houseNo}, ${companyData.value.zip} ${companyData.value.city}, ${companyData.value.country}`;
});

const updateProduct = () => {
    productTable.value.headers = [
        { key: 'image', title: ''},
        { key: 'name', title: t('productTableHeader.productName') },
        { key: 'company', title: t('productTableHeader.company') },
        { key: 'steps', title: t('productTableHeader.numberOfStep') },
        { key: 'inputs', title: t('productTableHeader.numberOfInput') },
        { key: 'actions', title: t('productTableHeader.actions') },
    ];

    productTable.value.data = products.value
        .filter((product: any) => {
            console.info(product.companies, companyId);

            return product.companies?.some((company: any) => company.id == companyId)

        }
        )
        .map((product: any) => {
            return {
                ...product,
                company: product.companies
                    ?.map((company: any) => company.name)
                    .join(', ') || '----',
                steps: product?.stepsTemplate?.steps?.length || 0,
                inputs: product?.stepsTemplate?.steps?.reduce((acc: number, step: any) => {
                    return acc + (step.inputs?.length || 0);
                }, 0) || 0,
                image: product.productImage
            };
        });
}

const addProduct = () => {
    router.push({ path: '/product/add-product', query: { companyId: companyId } });
}

const getRowData = (rowData: any) => {
    router.push(`/product/detail/${rowData.id}`)   
}

onMounted(() => {
    updateProduct()
})

</script>

<style lang="scss" scoped>
.detail-company {
    width: 100%;
}
</style>
