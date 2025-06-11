<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen } = useNuxtApp()
const { t } = useI18n()
const isDetailJsonModalOpen = ref(false)
const jsonAvailableDpps = ref<Record<string, string[]>>({})
const jsonAvailableCompanies = ref<Record<string, string>>({})
const jsonAvailableCompaniesNames = ref<string[]>([])
const jsonAvailableLogistics = ref<string[]>([])
const jsonSelectedDpps = ref<Record<string, string[]>>({})
const jsonSelectedCompanies = ref<string[]>([])
const jsonSelectedLogistics = ref<string[]>([])
const jsonData = ref()

const stepsStore = useStepsStore()
const productsStore = useProductsStore()
const nodesStore = useNodesStore()

const isLoading = ref({
    logistics: true,
    companies: true,
})

const showDppJson = async (nodeDpp: any) => {
    const stepsResults: any = {}
    let jsonCompanies: any[] = []

    if (!nodeDpp.steps) {
        return
    }

    for (const step of nodeDpp.steps) {
        if (!(step.id in stepsResults)) {
            const stepResponse = await stepsStore.fetchStepInputs(step.id)
            const ptId = stepResponse.productTemplate.substring(stepResponse.productTemplate.lastIndexOf('/') + 1)
            const companiesResponse = await productsStore.fetchCompanyProducts(ptId)

            stepsResults[step.id] = stepResponse
            jsonCompanies = [...new Set([...jsonCompanies, ...companiesResponse.companies.map(company => ({
                value: company.id,
                text: company.name,
            }))])]
        }
    }

    const dppStepsInputs = {
        id: null,
        node: nodeDpp.id,
        company: null,
        logistics: [],
        fromDpps: [],
        supplyChainTemplate: nodeDpp.supplyChainTemplate.substring(nodeDpp.supplyChainTemplate.lastIndexOf('/') + 1),
        steps: [],
    }

    const toNodeLogistics = []
    let fromLogisticsDpps: any[] = []

    const nodeLogisticsResponse = await nodesStore.fetchNodesFromDppLogistics(nodeDpp.id)

    for (const nodeLogistic of nodeLogisticsResponse.logistics) {
        const fromDppsIds = nodeLogistic.fromDpps.map(dpp => dpp.id)

        fromLogisticsDpps = [...fromLogisticsDpps, ...fromDppsIds]
        jsonAvailableDpps.value[nodeLogistic.id] = fromDppsIds
        jsonSelectedDpps.value[nodeLogistic.id] = []
        toNodeLogistics.push(nodeLogistic.id)
    }

    isLoading.value.companies = false

    let sequenceIndex = 0
    for (const productStep of nodeDpp.steps) {
        const quantity = productStep.quantity > 0 ? productStep.quantity : 1
        for (let i = 1; i <= quantity; i++) {
            sequenceIndex++

            const stepId = productStep.id
            const inputs = []

            if (!stepsResults[stepId]) {
                continue
            }

            const step = {
                id: stepId,
                name: productStep.name,
                sequenceIndex,
                quantityIndex: i,
                productTemplate: stepsResults[stepId].productTemplate.substring(stepsResults[stepId].productTemplate.lastIndexOf('/') + 1),
            }

            for (const stepInputNew of stepsResults[stepId].inputs) {
                const inputParameters = {
                    name: stepInputNew.name,
                    type: stepInputNew.type,
                }

                const type = stepInputNew.type.toLowerCase().replace(/\s+/g, '')
                switch (type) {
                    case 'numerical':
                        inputParameters.numericalValue = null
                        break
                    case 'text':
                        inputParameters.textValue = null
                        break
                    case 'textarea':
                        inputParameters.textAreaValue = null
                        break
                    case 'datetime':
                        inputParameters.dateTimeFrom = null
                        inputParameters.dateTimeTo = null
                        break
                    case 'coordinates':
                        inputParameters.latitudeValue = null
                        inputParameters.longitudeValue = null
                        break
                    default:
                        break
                }
                inputs.push({ ...inputParameters })
            }
            step.verified = false
            step.confirm = null

            step.inputs = [...inputs]
            dppStepsInputs.steps.push({ ...step })
        }
    }

    const companyIds = jsonCompanies.map(company => {
        return (company.value.substring(company.value.lastIndexOf('/') + 1))
    })

    const companyNames = jsonCompanies.map(company => {
        return company.text
    })

    jsonAvailableCompanies.value = [...jsonCompanies]
    jsonAvailableCompaniesNames.value = [...new Set(companyNames)]
    jsonAvailableLogistics.value = [...toNodeLogistics]
    isLoading.value.logistics = false

    const result = {
        dpp: { ...dppStepsInputs },
        ...({ availableCompanies: [...companyIds] }),
        ...({ availableLogistics: [...toNodeLogistics] }),
        ...({ availableFromDpps: [...fromLogisticsDpps] }),
    }

    jsonData.value = { action: JSON.stringify(result) }
}

$listen('openDetailJsonModal', async (node: any) => {
    isDetailJsonModalOpen.value = true

    await showDppJson(node)
})

const closeDetailJsonModal = () => {
    isDetailJsonModalOpen.value = false
    jsonSelectedLogistics.value = []
    jsonSelectedCompanies.value = []
}

const copyToClipboard = async (textToCopy: string) => {
    await navigator.clipboard.writeText(textToCopy)
}

const downloadJsonFile = (jsonDataDownload: string) => {
    if (!jsonDataDownload) {
        console.error('Data JSON is missing or invalid')

        return
    }

    try {
        const blob = new Blob([jsonDataDownload], { type: 'text/plain' })
        const link = document.createElement('a')

        link.href = URL.createObjectURL(blob)
        link.download = 'json.txt'
        link.click()
        URL.revokeObjectURL(link.href)
    } catch (error) {
        console.error('Error creating or downloading file:', error)
    }
}

watch(jsonSelectedDpps, newValue => {
    if (typeof jsonData.value?.action !== 'undefined') {
        const flattenedStrings = Object.values(newValue).flat()
        const parsedjsonDataAction = JSON.parse(jsonData.value.action)

        parsedjsonDataAction.dpp.fromDpps = [...flattenedStrings]
        jsonData.value = { action: JSON.stringify(parsedjsonDataAction) }
    }
}, { deep: true })

watch(jsonSelectedCompanies, newValue => {
    if (typeof jsonData.value?.action !== 'undefined') {
        const parsedjsonDataAction = JSON.parse(jsonData.value.action)

        const company = jsonAvailableCompanies.value.find(item => item.text === newValue)

        parsedjsonDataAction.dpp.company = company ? company.value : null
        jsonData.value = { action: JSON.stringify(parsedjsonDataAction) }
    }
})

watch(jsonSelectedLogistics, newValue => {
    if (typeof jsonData.value?.action !== 'undefined') {
        const parsedjsonDataAction = JSON.parse(jsonData.value.action)

        parsedjsonDataAction.dpp.logistics = [...newValue]
        jsonData.value = { action: JSON.stringify(parsedjsonDataAction) }
    }
})
</script>

<template>
    <ModalLayout
        :is-open="isDetailJsonModalOpen"
        name="detail-json-modal"
        :title="$t('json')"
        button-submit-text="Save"
        class="detail-json"
        width="55vw"
        @modal-close="closeDetailJsonModal"
    >
        <template #content>
            <div class="json-wrapper">
                <VRow>
                    <VCol cols="12">
                        <VSelect
                            v-model="jsonSelectedLogistics"
                            :items="jsonAvailableLogistics"
                            class="json-modal-select"
                            multiple
                            :label="$t('logistics.logistics')"
                            :placeholder="t('logistics.selectLogistics')"
                            variant="outlined"
                            :loading="isLoading.logistics"
                            :no-data-text="isLoading.logistics ? $t('dpps.loadingLogistics') : $t('noDataAvailable')"
                        />
                    </VCol>
                </VRow>

                <VRow
                    v-for="(logisticId, index) in jsonSelectedLogistics"
                    :key="index"
                >
                    <VCol cols="12">
                        <VLabel class="label">
                            {{ $t('logistics.header') }} {{ logisticId }} dpps:
                        </VLabel>
                        <VSelect
                            v-model="jsonSelectedDpps[logisticId]"
                            :items="jsonAvailableDpps[logisticId]"
                            class="json-modal-select"
                            multiple
                            :placeholder="$t('selectDpps')"
                            :loading="isLoading.companies"
                            :no-data-text="isLoading.companies ? $t('dpps.loadingCompanies') : $t('noDataAvailable')"
                        />
                    </VCol>
                </VRow>

                <VRow style="margin-bottom: 20px;">
                    <VCol cols="12">
                        <VSelect
                            v-model="jsonSelectedCompanies"
                            :items="jsonAvailableCompaniesNames"
                            class="json-modal-select"
                            :label="$t('companies.companies')"
                            :placeholder="t('companies.selectCompanies')"
                            variant="outlined"
                        />
                    </VCol>
                </VRow>

                <div
                    v-if="jsonData?.action"
                    class="popup-header copy-wrapper"
                >
                    <JsonFormatter :data-json="jsonData?.action">
                        <template #header>
                            <div class="copy-button-wrapper">
                                <div class="copy-button-box">
                                    <button
                                        class="copy-button"
                                        @click="copyToClipboard(jsonData.action)"
                                    >
                                        {{ $t('copy') }}
                                        <PhosphorIconCopySimple size="20" />
                                    </button>
                                    <button
                                        class="download-button"
                                        @click="downloadJsonFile(jsonData.action)"
                                    >
                                        {{ $t('download') }}
                                        <PhosphorIconFileArrowDown size="20" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </JsonFormatter>
                </div>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                height="45"
                @click="closeDetailJsonModal"
            >
                {{ $t('gotIt') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.detail-json.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        .modal-body {
            .form-wrapper {
                height: 400px;
                padding-top: 1rem;
                overflow-y: scroll;
            }
        }

        :global(.modal-footer) {
            margin-top: 2rem;
        }

        .modal-footer {
            .v-btn {
                padding-inline: 1rem;
                padding-block: 0.5rem;
                display: inline-block;
                border-radius: unset;
                flex: 1;
                transition: 0.5s all;

                &:hover {
                    background-color: rgba(167, 217, 212, 1) !important;
                    color: #000000 !important;
                }

                &.submit-btn {
                    background-color: rgba(38, 166, 154, 1);
                    color: #FFFFFF;
                    transition: 0.5s all;

                    &:hover {
                        background-color: rgba(167, 217, 212, 1);
                    }
                }
            }
        }
    }

    .copy-button-wrapper {
        position: relative;

        .copy-button-box {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.5rem 1rem;
            background-color: #D9D9D9;
            display: flex;
            align-items: center;
            .copy-button {
                margin-right: 0.5rem;
                display: flex;
            }

            .download-button {
                display: flex;
            }
        }
    }

}
</style>
