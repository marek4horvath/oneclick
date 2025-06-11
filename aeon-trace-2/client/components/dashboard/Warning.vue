<script setup lang="ts">
import type { WarningMessage } from '@/types/warningMessage'

defineProps<{
    data: WarningMessage[]
}>()
</script>

<template>
    <div class="warning-component">
        <!-- Render this component when there is only one warning -->
        <VRow
            v-if="data.length === 1"
            class="single-warning-row"
        >
            <VCol>
                <VCard
                    class="warning-container-single"
                    variant="flat"
                >
                    <VCard
                        class="warning-card align-center"
                        variant="flat"
                    >
                        <div class="card-content">
                            <div class="icon-background">
                                <PhosphorIconWarning
                                    :icon="data[0].icon"
                                    size="36"
                                    class="card-icon"
                                />
                            </div>
                            <span class="message">{{ data[0].message }}</span>
                        </div>
                    </VCard>
                </VCard>
            </VCol>
        </VRow>

        <!-- Render this component when there are multiple warnings -->
        <VRow
            v-else
            variant="flat"
            class="multiple-warning-row"
        >
            <VCard
                class="warning-container-multiple"
                variant="flat"
            >
                <VRow>
                    <VCol
                        v-for="item in data"
                        :key="item.message"
                    >
                        <VCard
                            class="warning-card align-center"
                            variant="flat"
                        >
                            <div class="card-content">
                                <div class="icon-background">
                                    <PhosphorIconWarning
                                        size="40"
                                        class="card-icon"
                                    />
                                </div>
                                <span class="message">{{ item.message }}</span>
                            </div>
                        </VCard>
                    </VCol>
                </VRow>
            </VCard>
        </VRow>
    </div>
</template>
