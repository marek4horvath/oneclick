<template>
    <NuxtLayout>
        <table-data v-model:search="search" :items="companyTable" @set:rowData="getRowData">
            <template #table-title>
                <v-text-field 
                    v-model="search" 
                    density="compact" 
                    :label="$t('search')" 
                    prepend-inner-icon="mdi-magnify"
                    variant="solo-filled" 
                    flat 
                    hide-details
                    single-line>
                </v-text-field>

                <v-spacer></v-spacer>

                <v-btn class="me-2" color="primary" @click="addCompany">{{ $t('companies.addCompany') }}</v-btn>
                <v-btn class="me-2" color="primary" @click="invateCompany">{{ $t('companies.invateCompany') }}</v-btn>
            </template>

            <template #table-actions="{ item }">
                <PhEye 
                    :size="20" 
                    color="#1967c0"
                    weight="light"  
                    class="cursor-pointer me-2"
                    @click="detailCompany(item)"
                />
                <PhGraph 
                    :size="20" 
                    color="#1967c0"
                    weight="light"  
                    class="cursor-pointer me-2"
                />
                
                <PhPencilSimpleLine 
                    :size="20" 
                    color="#1967c0" 
                    weight="light" 
                    class="cursor-pointer me-2"
                    @click="editCompany(item)"
                />
                <PhTrash 
                    :size="20" 
                    color="#1967c0" 
                    weight="light" 
                    class="cursor-pointer me-2"
                    @click="deleteCompany(item)"
                />
            </template>

            <template #table-image="{ item }">
                <v-img
                    v-if="item.image"
                    :src="item.image"
                    height="64"
                    cover
                    class="circle-img"
                ></v-img>
            </template>
        </table-data>
    </NuxtLayout>
</template>

<script setup lang="ts">
import type { CompanyPayload } from '~/interface/company';
import type { TableData } from '~/interface/tableData.ts';
import { PhEye, PhTrash, PhPencilSimpleLine, PhGraph } from "@phosphor-icons/vue";
import { useI18n } from 'vue-i18n'

const { $toast } = useNuxtApp()
const { t } = useI18n()
const router = useRouter()
const search: String = ref('')
const companyStore = useCompanyStore()
const companies: CompanyPayload[] = computed(() => companyStore.getCompanies)
const companyTable: TableData = ref({
    headers: [],
    data: [],
});

definePageMeta({
    title: 'Companies',
    name: 'companies',
    layout: 'dashboard',
    middleware: 'auth'
});

const updateCompany = () => {
    companyTable.value.headers = [
        { key: 'image', title: ''},
        { key: 'name', title: t('companyTableHeader.companyName') },
        { key: 'numberOfProduct', title: t('companyTableHeader.numberOfProduct') },
        { key: 'numberOfDpp', title: t('companyTableHeader.numberOfDpp') },
        { key: 'numberOfUser', title: t('companyTableHeader.numberOfUser') },
        { key: 'actions', title: t('companyTableHeader.actions') },
    ];

    companyTable.value.data = companies.value.map((company: CompanyPayload) => {
        return {
            ...company,
            image: company.companyLogo,
            numberOfProduct: companyStore.getProductsByCompany(company.id).length,
            numberOfDpp: 0,
            numberOfUser: 0
        };
    });
    
}

const addCompany = () => {
    router.push('/company/add-company')
}

const detailCompany = (data: any) => {
    router.push(`/company/detail/${data.id}`)
}

const getRowData = (rowData: any) => {  
    router.push(`/company/detail/${rowData.id}`)  
}

const editCompany = (data: any) => {
    router.push(`/company/edit-company/${data.id}`)
}

const deleteCompany = (data: any) => {
    companyStore.deleteCompanies(data.id)
    updateCompany()

    $toast.success(t('messages.deleteCompany'));
}

const invateCompany = () => {
    router.push('invate-company')
}

onMounted(() => {
    updateCompany()
})
</script>
