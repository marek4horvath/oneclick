<script setup lang="ts">
import type { WarningMessage } from '@/types/warningMessage'
import LogisticsSummary from '@/components/dashboard/LogisticsSummary.vue'
import ReportSummary from '@/components/dashboard/ReportSummary.vue'
import Warning from '@/components/dashboard/Warning.vue'

definePageMeta({
    title: 'page.dashboard.title',
    name: 'dashboard',
    layout: 'dashboard',
    middleware: 'auth',
})

const { t } = useI18n()
const warningData = ref<WarningMessage[] | null>(null)

warningData.value = [
    { message: t('messages.warningData'), icon: 'ri-alert-line' },
    { message: t('messages.warningData'), icon: 'ri-alert-line' },
    { message: t('messages.warningData'), icon: 'ri-alert-line' },
    { message: t('messages.warningData'), icon: 'ri-alert-line' },
]
</script>

<template>
    <NuxtLayout>
        <VContainer fluid>
            <div class="widgets-container">
                <ReportSummary />
                <Warning
                    v-if="warningData && warningData.length > 0"
                    :data="warningData"
                />
                <LogisticsSummary />
            </div>
        </VContainer>
    </NuxtLayout>
</template>
