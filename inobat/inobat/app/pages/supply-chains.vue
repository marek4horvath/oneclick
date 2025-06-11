<template>
    <NuxtLayout>
        <table-data v-model:search="search" :items="supplyTable" @set:rowData="getRowData">
            <template #table-title>
                <v-row >
                    <v-col cols="4">
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
                    </v-col>
                </v-row>
            </template>

            <template #table-actions="{ item }">
                <PhEye 
                    :size="20"
                    color="#1967c0"
                    weight="light"
                    class="cursor-pointer me-2"
                    @click="$router.push('/supply-chain/detail/' + item.id)"
                />

                <PhPencilSimpleLine 
                    :size="20"
                    color="#1967c0"
                    weight="light"
                    class="cursor-pointer me-2"
                />
            </template>

        </table-data>

    </NuxtLayout>
</template>

<script setup lang="ts">
import type { TableData } from '~/interface/tableData';
import { PhEye, PhPencilSimpleLine } from "@phosphor-icons/vue";
import type { SupplyChainPayload } from "~/interface/supplyChain"

const router = useRouter()
const supplyChainStore = useSupplyChainStore()
const supplyChans: SupplyChainPayload[] = computed(() => supplyChainStore.supplyChains)
const search: String = ref('')
const supplyTable:TableData = ref({
    headers: [],
    data: []
})

definePageMeta({
    title: 'Supply Chain',
    name: 'supply-chain',
    layout: 'dashboard',
    middleware: 'auth'
});

const getRowData = (rowData: any) => {
    router.push(`/supply-chain/detail/${rowData.id}`)   
}

const updateSupplyChain = () => {
    supplyTable.value.headers = [
        { key: 'name', title: 'name' },
        { key: 'numberOfNode', title: 'numberOfNode' },
        { key: 'actions', title: 'actions' },
    ];
    
    supplyTable.value.data = supplyChans.value.map((suppliChain: any) => {

        return {
            ...suppliChain,
            numberOfNode: suppliChain.nodes?.length || 0
        };
    });
}

onMounted(() => {
    updateSupplyChain()
})

</script>
