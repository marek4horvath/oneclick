<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen } = useNuxtApp()
const supplyChainStore = useSupplyChainStore()

const isVerifySupplyChainModalOpen = ref(false)
const isLoading = ref<boolean>(true)
const verifySupplyChain = ref<any[]>([])

$listen('openVerifySupplyChainModal', async (id: string) => {
    if (!id) {
        return
    }

    verifySupplyChain.value = []
    verifySupplyChain.value = await supplyChainStore.fetchSupplyChainTemplatesVerify(id)
    isLoading.value = false
    isVerifySupplyChainModalOpen.value = true
})

const closeVerifySupplyChainModal = () => {
    isVerifySupplyChainModalOpen.value = false
    verifySupplyChain.value = []
}
</script>

<template>
    <ModalLayout
        :is-open="isVerifySupplyChainModalOpen"
        name="verify-supply-chain-modal"
        title=""
        button-cancel-text="Cancel"
        button-submit-text="Close"
        disable-cancel-button
        style="overflow-y: scroll;"
        :width="!verifySupplyChain.verified ? '30vw' : '20vw'"
        class="verify-supply-chain"
        @modal-close="closeVerifySupplyChainModal"
    >
        <template #content>
            <div v-if="!isLoading">
                <div v-if="verifySupplyChain.verified && !verifySupplyChain?.info?.length">
                    <div class="text-center header successful">
                        <PhosphorIconCheckCircle
                            color="#00C15D"
                            :size="43"
                        />
                        <h2>
                            {{ $t('messages.infoVerifySupplyChainPositiv') }}
                        </h2>

                        <p>{{ $t('messages.verifySupplyChainPositivTitle') }}</p>
                    </div>
                </div>
                <div v-else>
                    <div class="text-center header">
                        <PhosphorIconXCircle
                            color="#E30004"
                            :size="43"
                        />
                        <h2>
                            {{ $t('messages.infoVerifySupplyChainNegative') }}
                        </h2>

                        <p>{{ $t('messages.verifySupplyChainNegativeTitle') }}</p>
                    </div>

                    <div
                        v-for="(verifyData, verifyIndex) in verifySupplyChain.info"
                        :key="verifyIndex"
                        class="info-verify"
                    >
                        <div class="product">
                            {{ verifyData.name }}
                        </div>

                        <ul class="info-verify-step">
                            <li
                                v-for="(step, stepIndex) in verifyData.steps"
                                :key="stepIndex"
                            >
                                {{ step.name }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div v-else />
        </template>

        <template #footer>
            <VBtn
                color="#26A69A"
                variant="flat"
                class="w-100 my-3"
                rounded="sm"
                @click.prevent="closeVerifySupplyChainModal"
            >
                {{ $t('gotIt') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>
