<template>
    <NuxtLayout>
        <v-container>
            <v-row>
                <v-col lg="4" sm="12">
                    <v-card color="primary" variant="tonal" class="mx-auto">
                        <v-card-item>
                            <div>
                                <div class="text-overline mb-1">
                                    {{ $t('dashboard.num_created_dpp') }}
                                </div>
                                <div class="text-h6 mb-1">
                                    2
                                </div>
                            </div>
                        </v-card-item>

                        <v-card-actions>
                            <v-btn @click="$router.push('/dpps')">
                                {{ $t('dashboard.show_dpps') }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-col>
                <v-col lg="4" sm="12">
                    <v-card color="primary" variant="tonal" class="mx-auto">
                        <v-card-item>
                            <div>
                                <div class="text-overline mb-1">
                                    {{ $t('dashboard.num_created_battery_packs') }}
                                </div>
                                <div class="text-h6 mb-1">
                                    2
                                </div>
                            </div>
                        </v-card-item>

                        <v-card-actions>
                            <v-btn @click="$router.push('/dpps')">
                                {{ $t('dashboard.show_battery_packs') }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-col>
                <v-col lg="4" sm="12">
                    <v-card color="primary" variant="tonal" class="mx-auto">
                        <v-card-item>
                            <div>
                                <div class="text-overline mb-1">
                                    {{ $t('dashboard.co2') }}
                                </div>
                                <div class="text-h6 mb-1">
                                    {{ products?.length || 0 }}
                                </div>
                            </div>
                        </v-card-item>

                        <v-card-actions>
                            <v-btn @click="$router.push('/products')">
                                {{ $t('dashboard.show_products') }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-col>
            </v-row>
            <v-row>
                <v-col lg="6" sm="12">
                    <v-card color="primary" variant="tonal" class="mx-auto">
                        <v-card-item>
                            <div>
                                <div class="text-overline mb-1">
                                    {{ $t('dashboard.monthly_produce') }}
                                </div>
                                <div class="mb-1" style="height: 300px;">
                                    <VChart :option="monthlyProduceOptions" />
                                </div>
                            </div>
                        </v-card-item>
                    </v-card>
                </v-col>
                <v-col lg="6" sm="12">
                    <v-card color="primary" variant="tonal" class="mx-auto">
                        <v-card-item>
                            <div>
                                <div class="text-overline mb-1">
                                    {{ $t('dashboard.co2_yearly') }}
                                </div>
                                <div class="mb-1" style="height: 300px;">
                                    <VChart :option="co2yearlyOptions" />
                                </div>
                            </div>
                        </v-card-item>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </NuxtLayout>
</template>

<script setup lang="ts">

import type { ProductPayload } from '~/interface/product';

const productStore = useProductStore()
const products: ProductPayload[] = computed(() => productStore.products)

definePageMeta({
    title: 'Dashboard',
    name: 'dashboard',
    layout: 'dashboard',
    middleware: 'auth'
});

const monthlyProduceOptions = ref<ECOption>({
    xAxis: {
        type: 'category',
        data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    },
    yAxis: {
        type: 'value'
    },
    series: [
        {
            data: [120, 200, 150, 80, 70, 110, 130, 100, 90, 80, 70, 60],
            type: 'bar'
        }
    ],
});

const co2yearlyOptions = ref<ECOption>({
    xAxis: {
        type: 'category',
        data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    },
    yAxis: {
        type: 'value'
    },
    series: [
        {
            data: [120, 200, 150, 80, 70, 110, 130, 100, 90, 80, 70, 60],
            type: 'bar'
        }
    ],
});
</script>
