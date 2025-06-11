<template>
    <NuxtLayout>
        <table-data v-model:search="search" :items="dppTable">
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
                    @click="$router.push('/dpp/detail/' + item.id)"
                />
                <PhQrCode 
                    :size="20"
                    color="#1967c0"
                    weight="light"
                    @click="openDialog('qrCode', item)"
                    class="cursor-pointer me-2"
                />
                <PhHash 
                    :size="20"
                    color="#1967c0"
                    weight="light"
                    @click="openDialog('idDpp', item)"
                    class="cursor-pointer me-2"
                />
            </template>

        </table-data>

        <Popup 
            v-model="dialogVisible" 
            :title="dialogTitle" 
            :content="dialogContent"
        >
            <template #popup-content>
                <div v-if="dialogType === 'qrCode'">
                    <v-img src="/assets/images/example-qr.png" class="mx-auto w-50 my-4"></v-img>
                </div>

            </template>
            <template #popup-actions>
                    <div v-if="dialogType === 'qrCode'">
                        <v-btn color="primary" variant="tonal"  text @click="printQr">
                            <PhQrCode 
                                :size="20"
                                color="#1967c0"
                                weight="light"
                                class="cursor-pointer me-2"
                            />
                            {{ $t('printQr') }}
                        </v-btn>
                        <v-btn color="primary" variant="tonal" class="ml-2">
                            <PhDownloadSimple 
                                :size="20"
                                color="#1967c0"
                                weight="light"
                                class="me-2"
                            />
                            {{ $t('download') }}

                            <v-menu activator="parent">
                                <v-list>
                                    <v-list-item  v-for="(item, index) in actionDownload" 
                                        :key="index"
                                    >
                                        <v-list-item-title>
                                            <a
                                                :href="qrUrl"
                                                :download="downloadFileName"
                                                class="text-decoration-none"
                                            >
                                                <v-btn 
                                                    color="primary"
                                                    block
                                                    type="button"
                                                >
                                                    <PhDownloadSimple 
                                                        :size="20"
                                                        color="#fff"
                                                        weight="light"
                                                        class="me-2"
                                                    />
                                                    {{ item.title }}
                                                </v-btn>
                                            </a>
                                        </v-list-item-title>
                                    </v-list-item>
                                </v-list>
                            </v-menu>
                        </v-btn>
                    </div>
            </template>
        </Popup>
    </NuxtLayout>
    <print-template
        :qr-src="qrUrl"
        class="d-none"
    />

</template>

<script setup lang="ts">

import type { TableData } from '~/interface/tableData';
import { PhEye, PhHash, PhQrCode, PhDownloadSimple } from "@phosphor-icons/vue";
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const search: String = ref('')
const dppTable:TableData = ref({
    headers: [
        { key: 'name', title: t('dppTableHeader.name') },
        { key: 'numberOfInputs', title: t('dppTableHeader.numberOfInputs') },
        { key: 'createdAt', title: t('dppTableHeader.createdAt') },
        { key: 'tsaVerified', title: t('dppTableHeader.tsaVerified') },
        { key: 'actions', title: t('dppTableHeader.actions') },
    ],
    data: [
        {
            id: '66fb3085',
            name: 'Test dpps',
            numberOfInputs: 4,
            createdAt: '11/12/2024, 10:59:51',
            tsaVerified: '11/12/2024, 10:59:51',
        }
    ],
});

const qrUrl: String = ref('http://localhost:3000/_nuxt/assets/images/example-qr.png')

const actionDownload = ref([
    { title: `${t('download')} PNG`, type: 'png' },
    { title: `${t('download')} SVG`, type: 'svg' },
])

const downloadFileName = ref<string>(`dpp-66fb3085`)

const dialogVisible = ref(false);
const dialogTitle = ref('');
const dialogContent = ref('');
const dialogType = ref('');


definePageMeta({
    title: 'Dpps',
    name: 'dpps',
    layout: 'dashboard',
    middleware: 'auth'
});

const openDialog = (type: string, data?: any) => {
    dialogVisible.value = true;

    switch (type) {
        case 'qrCode':
            dialogTitle.value = 'QR Code';
            dialogType.value = type

            break;
        case 'idDpp':
            dialogTitle.value = data.id;
            dialogType.value = type
                
            break;
        default:
            dialogTitle.value = 'Unknown Action';
            dialogContent.value = 'No content available for this action.';
    }

}

const printQr = () => {
    const printWindow = window.open('', '', 'height=600,width=800');
    const content = document.getElementById('printable-content')?.innerHTML;

    if (!content) {
        console.error('Content not found!');
        return;
    }

    const styles = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
        .map((style) => style.outerHTML)
        .join('\n');

    printWindow.document.write(`
        <html>
            <head>
                <title>Print</title>
                ${styles}
            </head>
            <body>
                ${content}
            </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.onload = () => {
        printWindow.print();
        printWindow.close();
    };
};



</script>
