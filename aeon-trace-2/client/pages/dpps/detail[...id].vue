<script setup lang="ts">
import download from 'downloadjs'
import Map from '~/components/Map.vue'
import type { SelectItem } from '@/types/selectItem'
import { formattedDate } from '@/helpers/formattedDate'
import DetailDpp from '~/dialogs/dpps/detail-dpp.vue'
import HistoryInput from '~/dialogs/dpps/history-input.vue'
import EditProductInput from '~/dialogs/dpps/edit-product-input.vue'
import { formatPascalCaseToLabel, formatText } from '@/helpers/textFormatter'
import EditDpp from '~/dialogs/dpps/edit-dpp.vue'

definePageMeta({
    title: 'page.dppsDetail.title',
    name: 'dpps-detail',
    layout: 'dashboard',
    middleware: 'auth',
})

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const urlDocument = ref<string>('')
const urlImage = ref<string>('')
const urlImages = ref<string>('')

const route = useRoute()
const router = useRouter()
const dppStore = useDppStore()
const logisticsStore = useLogisticsStore()
const productsStore = useProductsStore()
const nodeStore = useNodesStore()

const dpp = ref(null)
const logistic = ref(null)

const MEASUREMENT_TYPE = 'MEASUREMENT_TYPE'

const inputs = ref({})
const mapCoordinates = ref([])
const node = ref([])
const nodes = ref([])

const dppItems = ref<SelectItem[]>([])
const selectedDpp = ref(null)
const fullscreen = ref(false)
const activeStepIds = ref([])
const mediaPath = ref('')

const legendData = ref({
    dpps: [
        { color: '#66FF07', name: t('dppDetail.notAssignedDpp') },
        { color: '#FFA500', name: t('dppDetail.ongoingDpp') },
        { color: '#3498DB', name: t('dppDetail.dppLogisticsAssigned') },
        { color: '#FF007F', name: t('dppDetail.dppInUse') },
        { color: '#FF0000', name: t('dppDetail.exportedDpp') },
    ],
    logistics: [
        { color: '#3498DB', name: t('dppDetail.logisticWaitingExported') },
        { color: '#FF0000', name: t('dppDetail.exportedLogistics') },
    ],
})

const isProductStepType = computed(() => {
    return route.query.type === 'product-step'
})

const pairs = ref<Record<string, any>>({})

const fetchTraceData = async () => {
    const id = route.params.id[1]

    try {
        if (isProductStepType.value) {
            const traceData = await productsStore.fetchProductInputMaterialsTrace(id)
            if (traceData) {
                pairs.value = Object.values(traceData)
            }
        } else {
            const traceData = await dppStore.fetchDppMaterialsTrace(id)
            if (traceData) {
                pairs.value = Object.values(traceData)
            }
        }
    } catch (error) {
        console.error('Error fetching trace data:', error)
        pairs.value = {}
    }
}

const addressGroups = computed(() => {
    const groups: any[] = []

    Object.values(pairs.value).forEach((pairsValue: any) => {
        pairsValue.forEach((pair: any) => {
            const addresses = []

            if (pair[0]) {
                addresses.push({
                    lat: Number.parseFloat(pair[0].coords.lat),
                    lng: Number.parseFloat(pair[0].coords.lng),
                })
            }

            if (pair[1]) {
                addresses.push({
                    lat: Number.parseFloat(pair[1].coords.lat),
                    lng: Number.parseFloat(pair[1].coords.lng),
                })
            }

            if (addresses.length > 0) {
                groups.push({
                    addresses,
                    color: "blue",
                    connectLine: true,
                })
            }
        })
    })

    return groups
})

const findOccurrences = (data: Record<string, any>, searchId: string) => {
    const results: Record<string, number> = {}

    Object.entries(data).forEach(([key, section]) => {
        let count = 0
        if (Array.isArray(section.data)) {
            count = section.data.filter((item: any) => item["@id"].includes(searchId)).length
        } else if (typeof section.data === 'object') {
            count = Object.values(section.data).filter((item: any) => item["@id"].includes(searchId)).length
        }
        if (count > 0) {
            results[key] = count
        }
    })

    return results
}

const loadNodeHierarchy = async (nodeId: string) => {
    const nodesSet = new Set()

    node.value = [await nodeStore.fetchNode(nodeId as string)]

    if (dpp.value.parentDpps?.length) {
        dppItems.value = dpp.value.parentDpps[0]?.dpps.map((dppItem: any) => {
            return {
                value: dppItem.id,
                title: `${dppItem.id} - ${dppItem.name}`,
            }
        })

        const parentNodeId = dpp.value?.parentDpps?.[0]?.node?.['@id']

        if (parentNodeId) {
            const filteredParents = node.value[0].parents.filter((parent: any) => parent['@id'] === parentNodeId)

            if (filteredParents.length > 0) {
                filteredParents[0].countLogistics = {
                    assignedToDpp: 0,
                    exportLogistics: node.value[0].countLogistics.exportLogistics > 0 ? 1 : 0,
                    exportLogisticsData: node.value[0].countLogistics.exportLogisticsData.length > 1
                        ? [node.value[0].countLogistics.exportLogisticsData[0]]
                        : node.value[0].countLogistics.exportLogisticsData,
                    assignedToDppData: [],

                }

                filteredParents[0].countDpp = {
                    exportDpp: dpp.value?.parentDpps?.[0]?.node?.countDpp.exportDpp > 0 ? 1 : 0,
                }

                node.value[0].countDpp = {
                    dppInUse: node.value[0].countDppData.dppInUse.count > 0 ? 1 : 0,
                    logistics: filteredParents[0].countDpp.exportDpp === 0 ? (node.value[0].countDppData.logistics.count > 0 ? 1 : 0) : 0,
                }

                const parentNode = { ...filteredParents[0] }

                node.value[0].countLogistics = {
                    assignedToDpp: 0,
                    exportLogistics: node.value[0].countLogistics.exportLogistics > 0 ? 1 : 0,
                    exportLogisticsData: node.value[0].countLogistics.exportLogisticsData.length > 1
                        ? [node.value[0].countLogistics.exportLogisticsData[0]]
                        : node.value[0].countLogistics.exportLogisticsData,
                    assignedToDppData: [],
                }

                parentNode.children = [{ ...node.value[0] }]
                parentNode.parents = []
                node.value[0].parents = node.value[0].countLogistics
                node.value[0].parents = [parentNode]
                node.value.push(parentNode)
            }
        }
    }

    node.value = node.value.map((nodeItem: any) => {
        if (nodeItem?.countDppData && isProductStepType.value) {
            nodeItem.countDpp = {
                notAssignedDpp: nodeItem?.countDppData?.notAssignedDpp?.count > 0 ? 1 : 0,
            }
        }
        if (!dpp.value.parentDpps?.length) {
            nodeItem.countDpp = findOccurrences(nodeItem.countDppData, route.params.id[1])
        }

        if (isProductStepType.value) {
            nodeItem.countDpp.notAssignedDpp = nodeItem.countDppData?.notAssignedDpp?.count > 0 ? 1 : 0
        }

        return {
            ...nodeItem,
            processColor: nodeItem.typeOfProcess.color,
            process: nodeItem.typeOfProcess?.name,
        }
    })

    nodes.value = Array.from(nodesSet)
}

const dppDetail = async (dppId: string = route.params.id[1]) => {
    if (route.query.type === 'dpp') {
        dpp.value = await dppStore.fetchDpp(dppId as string)

        if (dpp.value?.materialsSentWith) {
            logistic.value = await logisticsStore.fetchLogisticsById(dpp.value?.materialsSentWith?.split('/').pop() as string)
        }

        for (const step of dpp.value?.productSteps) {
            if (!inputs.value[step.id]) {
                inputs.value[step.id] = {
                    step,
                    inputs: step.productInputs,
                }
            }

            const coordinatesInputs = step.productInputs.filter((input: any) => input.type === 'coordinates')

            if (coordinatesInputs.length > 0) {
                mapCoordinates.value[step.id] = coordinatesInputs.map((input: any) => ({
                    lat: input.latitudeValue,
                    lng: input.longitudeValue,
                }))
            }
        }

        await loadNodeHierarchy(dpp.value?.node['@id']?.split('/').pop())

        mediaPath.value = 'dpp_qrs'
    }

    if (isProductStepType.value) {
        dpp.value = await productsStore.fetchProductStepById(dppId as string)

        if (dpp.value?.materialsSentWith) {
            logistic.value = await logisticsStore.fetchLogisticsById(dpp.value?.materialsSentWith?.split('/').pop() as string)
        }

        inputs.value[dpp.value.id] = {
            step: dpp.value,
            inputs: dpp.value?.productInputs || [],
        }

        const coordinatesInputs = dpp.value?.productInputs.filter((input: any) => input.type === 'coordinates')

        if (coordinatesInputs.length > 0) {
            mapCoordinates.value[dpp.value.id] = coordinatesInputs.map((input: any) => ({
                lat: input.latitudeValue,
                lng: input.longitudeValue,
            }))
        }

        await loadNodeHierarchy(dpp.value?.nodeId)

        mediaPath.value = 'step_qrs'
    }

    router.currentRoute.value.meta.title = dpp.value?.name || 'DPP'
}

const toggleAccordion = (stepId: string) => {
    const index = activeStepIds.value.indexOf(stepId)
    if (index === -1) {
        activeStepIds.value.push(stepId)
    } else {
        activeStepIds.value.splice(index, 1)
    }
}

const isActive = (stepId: string) => {
    return activeStepIds.value.includes(stepId)
}

const openFullscreen = () => {
    fullscreen.value = true
}

const closeFullscreen = () => {
    fullscreen.value = false
}

const downloadQr = () => {
    const link = document.createElement('a')

    link.href = `${backendUrl.value}/media/${mediaPath.value}/${dpp.value.qrImage}`
    link.download = 'qr-code.png'
    link.click()
}

const printQr = () => {
    const printWindow = window.open('', '', 'height=600,width=800')
    const content = document.getElementById('printable-content')?.innerHTML

    if (!printWindow || !content) {
        return
    }

    printWindow.document.write('<html><head><title>Print</title></head><body >')
    printWindow.document.write(content)
    printWindow.document.write('</body></html>')
    printWindow.document.close()
    printWindow.focus()
    printWindow.print()
}

const isVideo = (filename: string) => {
    const videoExtensions = ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm']
    const fileExtension = filename.split('.').pop()?.toLowerCase()

    return fileExtension && videoExtensions.includes(fileExtension)
}

const downloadFile = async (fileUrl: string, filename: string) => {
    try {
        const fullUrl = fileUrl.startsWith('http')
            ? fileUrl
            : `${window.location.origin}${fileUrl}`

        const response = await fetch(fullUrl)

        if (!response.ok) {
            const text = await response.text()

            console.error('Unexpected response:', text)
            throw new Error('File download failed')
        }

        const blob = await response.blob()

        download(blob, filename)
    } catch (error) {
        console.error('Download failed:', error)
    }
}

$listen('closeDetailDppModal', (isOpen: boolean) => {
    if (!isOpen) {
        selectedDpp.value = null
    }
})

const handleEditDpp = async (dppData: any) => {
    const dppResponse = await dppStore.fetchdppListingView(dppData.id)

    $event('openEditDppModal', dppResponse)
}

watch(selectedDpp, (newValue: any) => {
    if (!newValue) {
        return
    }

    $event('openDetailDppModal', { id: newValue, type: route.query.type })
})

const history = (productInputId: string) => {
    $event('openHistoryInputModal', productInputId)
}

const editInput = async (productInput: any, step: string | object, productStep: string) => {
    let stepId

    if (typeof step === 'string') {
        stepId = step.split('/').pop()
    }

    if (typeof step === 'object') {
        stepId = step.id
    }

    $event('openEditProductInputModal', { productInput, stepId, productStep })
}

$listen('editProductInputUpdate', data => {
    if (data.success) {
        const stepInputs = inputs.value[data.productStepId]?.inputs

        if (stepInputs && Array.isArray(stepInputs)) {
            const index = stepInputs.findIndex((item: any) => item.id === data.editData.id)

            if (index !== -1) {
                stepInputs[index] = {
                    ...stepInputs[index],
                    ...data.editData,
                }
            }
        }
    }
})

onMounted(async () => {
    urlDocument.value = `${backendUrl.value}/media/product_input_documents/`
    urlImage.value = `${backendUrl.value}/media/product_input_images/`
    urlImages.value = `${backendUrl.value}/media/product_input_collection_images/`

    await dppDetail()
    await fetchTraceData()
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer
            fluid
            class="detail-dpp"
        >
            <VRow>
                <VCol>
                    <header>
                        <VSelect
                            v-model="selectedDpp"
                            :items="dppItems"
                            class="w-50"
                            :label="$t('dpps.selectDpp')"
                            :placeholder="$t('dpps.selectDpp')"
                            variant="outlined"
                        />
                    </header>
                </VCol>
                <VCol>
                    <VRow>
                        <VCol>
                            <VImg
                                v-if="dpp?.company?.companyLogo"
                                :src="`${backendUrl}/media/company_logos/${dpp?.company?.companyLogo}`"
                                height="162"
                                width="162"
                                max-height="162"
                                max-width="162"
                                class="dpp-company-logo"
                            />
                        </VCol>
                        <VCol>
                            <span class="dpp-version">v1.00.01</span>
                        </VCol>
                    </VRow>
                </VCol>
            </VRow>

            <div
                v-if="dpp"
                class="mb-5 summary"
            >
                <div class="header">
                    {{ $t('dpps.summary') }}
                </div>
                <div>
                    <VRow>
                        <VCol class="summary-info">
                            <p class="font-weight-bold">
                                {{ $t('dpps.name') }}:  {{ dpp?.name }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.uidd') }}:  {{ dpp?.id }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.company') }}: {{ dpp?.company?.name }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.createdBy') }}: {{ dpp?.userData?.firstName }} {{ dpp?.userData?.lastName }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.createdAt') }}: {{ formattedDate(dpp?.createdAt) }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.logistic') }}:
                                <span
                                    class="summary-link"
                                    @click="router.push(`/dpps/logistics/${logistic?.id}`)"
                                >
                                    {{ logistic?.name || logistic?.id }}
                                </span>
                            </p>

                            <p class="d-flex align-center ga-2">
                                {{ $t('dppDetail.logisticsAssigned') }}:
                                <PhosphorIconCheckCircle
                                    v-if="dpp.materialsSentWith"
                                    :size="32"
                                    color="#24a69a"
                                />

                                <PhosphorIconWarningCircle
                                    v-else
                                    :size="32"
                                    color="#ff0000"
                                />
                            </p>

                            <p
                                v-if="dpp?.state"
                                class="font-weight-bold"
                            >
                                {{ $t('dpps.stateTitle') }}: {{ formatText(dpp?.state) }}
                            </p>
                        </VCol>

                        <VCol>
                            <div
                                v-if="dpp.qrImage"
                                class="qr-code"
                            >
                                <div class="actions">
                                    <PhosphorIconPrinter
                                        size="24"
                                        @click="printQr"
                                    />
                                    <PhosphorIconDownloadSimple
                                        size="24"
                                        @click="downloadQr"
                                    />
                                    <PhosphorIconArrowsOut
                                        size="24"
                                        @click="openFullscreen"
                                    />

                                    <PhosphorIconPencil
                                        v-if="(dpp.updatable || dpp?.updatableSteps) || (dpp.ongoingDpp || dpp.createEmptyDpp)"
                                        :size="20"
                                        @click="handleEditDpp(dpp)"
                                    />
                                </div>
                                <VImg
                                    :src="`${backendUrl}/media/${mediaPath}/${dpp.qrImage}`"
                                    height="162"
                                    width="162"
                                    max-height="162"
                                    max-width="162"
                                    class="cursor-pointer"
                                    @click="openFullscreen"
                                />
                            </div>
                        </VCol>
                    </VRow>
                </div>
            </div>

            <VCard
                v-for="inputGroup in inputs"
                :key="inputGroup.step?.id"
                class="mb-4 rounded-lg step"
            >
                <VCardTitle
                    class="header"
                    :style="isActive(inputGroup.step?.id) ? 'border-bottom: 1px solid;' : 'border-bottom: unset'"
                    @click="toggleAccordion(inputGroup.step?.id)"
                >
                    {{ $t('dpps.inputs') }}
                    <template v-if="inputGroup.step">
                        {{ $t('dppDetail.forStep', { name: inputGroup.step?.name }) }}
                        <PhosphorIconCaretDown v-if="!isActive(inputGroup.step?.id)" />
                        <LazyPhosphorIconCaretUp v-if="isActive(inputGroup.step?.id)" />
                    </template>
                </VCardTitle>
                <VCardText
                    v-show="isActive(inputGroup.step?.id)"
                    class="mt-4"
                >
                    <VRow>
                        <VCol cols="12">
                            <h3 class="additional-input-title">
                                Default inputs:
                            </h3>
                        </VCol>
                        <VCol
                            v-if="inputGroup.step.measurementValue !== 0"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <div>
                                {{ `${formatPascalCaseToLabel(inputGroup.step.measurementType)}${inputGroup.step.unitMeasurement ? ` (${inputGroup.step.unitMeasurement})` : ''}` }}
                                <div class="font-weight-bold">
                                    {{ inputGroup.step.measurementValue }}
                                </div>
                            </div>
                        </VCol>
                        <VCol
                            v-for="input in inputGroup.inputs.filter((i: any) => !i.additional)"
                            :key="input.id"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <div class="d-flex align-center">
                                <p>
                                    {{ input.name }}:
                                </p>
                                <div class="mx-2">
                                    <span :title="$t('actionsTablesTitle.updateInput')">
                                        <PhosphorIconRepeat
                                            v-if="input.updatable"
                                            :size="24"
                                            class="cursor-pointer mx-2"
                                            color="#888888"
                                            @click="editInput(input, inputGroup.step.stepTemplateReference, inputGroup.step.id)"
                                        />
                                    </span>

                                    <span :title="$t('actionsTablesTitle.history')">
                                        <PhosphorIconClockCounterClockwise
                                            v-if="input.updatable"
                                            :size="24"
                                            class="cursor-pointer"
                                            color="#888888"
                                            @click="history(input.id)"
                                        />
                                    </span>
                                    <PhosphorIconDownloadSimple
                                        v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'file' || input.type.toLowerCase().replace(/\s+/g, '') === 'image'"
                                        :size="24"
                                        class="cursor-pointer"
                                        color="#888888"
                                        @click="downloadFile(
                                            input?.document ? urlDocument + input?.document : urlImage + input.image,
                                            input?.document ? input?.document : input.image,
                                        )"
                                    />
                                </div>
                            </div>
                            <p class="font-weight-bold">
                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'numerical'">
                                    <span v-if="!input.measurementType">
                                        {{ input.numericalValue }}
                                    </span>
                                    <span v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </span>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'text'">
                                    {{ input.textValue !== MEASUREMENT_TYPE ? input.textValue : '' }}

                                    {{ input.measurementType
                                        ? `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}` : ''
                                    }}
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'file'">
                                    <div v-if="input.document && !input.measurementType">
                                        <div v-if="isVideo(input.document)">
                                            <video
                                                width="450"
                                                height="240"
                                                controls
                                            >
                                                <source
                                                    :src="urlDocument + input.document"
                                                    type="video/mp4"
                                                >

                                            </video>
                                        </div>
                                        <div v-else>
                                            <p>
                                                <PhosphorIconFileText
                                                    size="100"
                                                    class="cursor-pointer"
                                                    @click="downloadFile(urlDocument + input.document, input.document)"
                                                />
                                            </p>
                                        </div>
                                    </div>
                                    <div v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </div>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'textarea'">
                                    {{ input.textAreaValue !== MEASUREMENT_TYPE ? input.textAreaValue : '' }}
                                    {{ input.measurementType
                                        ? `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}` : ''
                                    }}
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'datetimerange'">
                                    <div v-if="!input.measurementType">
                                        <p>
                                            {{ $t('dpps.dateTimeFrom') }} : {{ formattedDate(input.dateTimeFrom) }}
                                        </p>
                                        <p>
                                            {{ $t('dpps.dateTimeTo') }}  : {{ formattedDate(input.dateTimeTo) }}
                                        </p>
                                    </div>
                                    <div v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </div>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'datetime'">
                                    <p v-if="!input.measurementType">
                                        {{ formattedDate(input.dateTimeTo) }}
                                    </p>
                                    <p v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </p>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'image'">
                                    <div
                                        v-if="!input.measurementType"
                                        class="image"
                                    >
                                        <VImg
                                            :src="urlImage + input.image"
                                            class="w-100 mx-auto"
                                        />
                                    </div>
                                    <div v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </div>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'images'">
                                    <div
                                        v-if="!input.measurementType"
                                        class="image-grid"
                                    >
                                        <div
                                            v-for="image in input.images"
                                            :key="image.id"
                                            class="image-item"
                                        >
                                            <VImg :src="urlImages + image.image" />

                                            <div
                                                class="download-wraper cursor-pointer"
                                                @click="downloadFile(urlImages + image.image, image.image)"
                                            >
                                                <PhosphorIconDownloadSimple
                                                    :size="50"
                                                    class="cursor-pointer download-icon"
                                                    color="#fff"
                                                    @click="downloadFile(urlImages + image.image, image.image)"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </div>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'coordinates'">
                                    <div v-if="!input?.measurementType">
                                        {{ $t('address.latitude') }}: {{ input.latitudeValue }}<br>
                                        {{ $t('address.longitude') }}: {{ input.longitudeValue }}
                                        <VCard
                                            v-if="mapCoordinates[inputGroup.step.id].length > 0"
                                            class="map"
                                            variant="flat"
                                        >
                                            <Map
                                                :key="inputGroup.step.id"
                                                :is-active-map="false"
                                                :is-marker-clicked="false"
                                                :zoom="5"
                                                :address-groups="[
                                                    {
                                                        addresses: [{
                                                            lat: input.latitudeValue,
                                                            lng: input.longitudeValue,
                                                        }],
                                                        color: 'blue',
                                                        connectLine: false,
                                                    },
                                                ]"
                                            />
                                        </VCard>
                                    </div>

                                    <div v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </div>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'radiolist'">
                                    <span v-if="!input?.measurementType">
                                        {{ input.radioValue }}
                                    </span>
                                    <span v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </span>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'checkboxlist'">
                                    <span v-if="!input?.measurementType">
                                        {{ input.checkboxValue.join(', ') }}
                                    </span>
                                    <span v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </span>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'textlist'">
                                    <span v-if="!input?.measurementType">
                                        {{ input.textValue }}
                                    </span>
                                    <span v-else>
                                        {{
                                            `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                        }}
                                    </span>
                                </template>
                            </p>
                        </VCol>
                    </VRow>
                    <template v-if="inputGroup.inputs.some(i => i.additional)">
                        <VDivider class="my-4" />
                        <VRow>
                            <VCol cols="12">
                                <h3 class="additional-input-title">
                                    {{ $t('dpps.additionalInputs') }}:
                                </h3>
                            </VCol>
                            <VCol
                                v-for="input in inputGroup.inputs.filter((i: any) => i.additional)"
                                :key="input.id"
                                cols="12"
                                lg="4"
                                md="6"
                            >
                                <div class="d-flex align-center">
                                    <p>
                                        {{ input.name }}:
                                    </p>
                                    <div class="mx-2">
                                        <LazyPhosphorIconPencilSimpleLine
                                            v-if="input.updatable"
                                            :size="24"
                                            class="cursor-pointer mx-2"
                                            color="#888888"
                                            @click="editInput(input, inputGroup.step.stepTemplateReference, inputGroup.step.id)"
                                        />

                                        <PhosphorIconClockCounterClockwise
                                            v-if="input.updatable && input.history?.length"
                                            :size="24"
                                            class="cursor-pointer"
                                            color="#888888"
                                            @click="history(input.id)"
                                        />

                                        <PhosphorIconDownloadSimple
                                            v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'file' && !isVideo(input.document)"
                                            :size="24"
                                            class="cursor-pointer"
                                            color="#888888"
                                            @click="downloadFile(urlDocument + input.document, input.document)"
                                        />
                                    </div>
                                </div>
                                <p class="font-weight-bold">
                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'numerical'">
                                        <span v-if="!input.measurementType">
                                            {{ input.numericalValue }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'text'">
                                        {{ input.textValue !== MEASUREMENT_TYPE ? input.textValue : '' }}

                                        {{ input.measurementType
                                            ? `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}` : ''
                                        }}
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'file'">
                                        <div v-if="input.document && !input.measurementType">
                                            <div v-if="isVideo(input.document)">
                                                <video
                                                    width="450"
                                                    height="240"
                                                    controls
                                                >
                                                    <source
                                                        :src="urlDocument + input.document"
                                                        type="video/mp4"
                                                    >

                                                </video>
                                            </div>
                                            <div v-else>
                                                <p>
                                                    <PhosphorIconFileText
                                                        size="100"
                                                        class="cursor-pointer"
                                                        @click="downloadFile(urlDocument + input.document, input.document)"
                                                    />
                                                </p>
                                            </div>
                                        </div>
                                        <div v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </div>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'textarea'">
                                        {{ input.textAreaValue !== MEASUREMENT_TYPE ? input.textAreaValue : '' }}
                                        {{ input.measurementType
                                            ? `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}` : ''
                                        }}
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'datetimerange'">
                                        <div v-if="!input.measurementType">
                                            <p>
                                                {{ $t('dpps.dateTimeFrom') }} : {{ formattedDate(input.dateTimeFrom) }}
                                            </p>
                                            <p>
                                                {{ $t('dpps.dateTimeTo') }}  : {{ formattedDate(input.dateTimeTo) }}
                                            </p>
                                        </div>
                                        <div v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </div>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'datetime'">
                                        <p v-if="!input.measurementType">
                                            {{ formattedDate(input.dateTimeTo) }}
                                        </p>
                                        <p v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </p>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'image'">
                                        <div
                                            v-if="!input.measurementType"
                                            class="image"
                                        >
                                            <VImg
                                                :src="urlImage + input.image"
                                                class="w-100 mx-auto"
                                            />
                                        </div>
                                        <div v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </div>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'images'">
                                        <div
                                            v-if="!input.measurementType"
                                            class="image-grid"
                                        >
                                            <div
                                                v-for="image in input.images"
                                                :key="image.id"
                                                class="image-item"
                                            >
                                                <VImg :src="urlImages + image.image" />
                                                <div
                                                    class="download-wraper cursor-pointer"
                                                    @click="downloadFile(urlImages + image.image, image.image)"
                                                >
                                                    <PhosphorIconDownloadSimple
                                                        :size="50"
                                                        class="cursor-pointer download-icon"
                                                        color="#fff"
                                                        @click="downloadFile(urlImages + image.image, image.image)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </div>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'coordinates'">
                                        <div v-if="!input?.measurementType">
                                            {{ $t('address.latitude') }}: {{ input.latitudeValue }}<br>
                                            {{ $t('address.longitude') }}: {{ input.longitudeValue }}

                                            <VCard
                                                v-if="mapCoordinates[inputGroup.step.id].length > 0"
                                                class="map"
                                                variant="flat"
                                            >
                                                <Map
                                                    :is-active-map="false"
                                                    :is-marker-clicked="false"
                                                    :zoom="5"
                                                    :address-groups="[
                                                        {
                                                            addresses: [{
                                                                lat: input.latitudeValue,
                                                                lng: input.longitudeValue,
                                                            }],
                                                            color: 'blue',
                                                            connectLine: false,
                                                        },
                                                    ]"
                                                />
                                            </VCard>
                                        </div>

                                        <div v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </div>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'radiolist'">
                                        <span v-if="!input?.measurementType">
                                            {{ input.radioValue }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'checkboxlist'">
                                        <span v-if="!input?.measurementType">
                                            {{ input.checkboxValue.join(', ') }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </template>

                                    <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'textlist'">
                                        <span v-if="!input?.measurementType">
                                            {{ input.textValue }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </template>
                                </p>
                            </VCol>
                        </VRow>
                    </template>
                </VCardText>
            </VCard>

            <Legend
                style="width: 100%"
                class="my-8"
                :data="legendData.dpps"
                :legend-name="t('legend.dpp')"
                is-square
            />

            <Legend
                style="width: 100%"
                class="my-8"
                :legend-name="$t('legend.logistics')"
                :data="legendData.logistics"
            />

            <VCard
                variant="flat"
                class="mt-4"
            >
                <TreeFlow
                    :data="node"
                    connection-key="parents"
                    traversal="forward"
                    disable-options
                />
            </VCard>
            <div>
                <VCard
                    v-if="Object.keys(pairs).length > 0"
                    variant="flat"
                    class="mt-4"
                >
                    <VCardTitle class="header">
                        Transport Routes
                    </VCardTitle>
                    <Map
                        :is-active-map="false"
                        :is-marker-clicked="false"
                        :zoom="4"
                        :address-groups="addressGroups"
                        show-markers
                        is-trace
                    />
                </VCard>
            </div>
        </VContainer>

        <div
            v-if="fullscreen"
            class="fullscreen-overlay"
            @click="closeFullscreen"
        >
            <VImg
                :src="`${backendUrl}/media/${mediaPath}/${dpp.qrImage}`"
                class="fullscreen-image"
            />
        </div>

        <PrintTemplate
            v-if="dpp"
            :qr-src="`${backendUrl}/media/${mediaPath}/${dpp.qrImage}`"
            class="hidden"
        />
    </NuxtLayout>
    <DetailDpp />
    <EditDpp />
    <HistoryInput />
    <EditProductInput />
</template>

<style lang="scss" scoped>
.detail-dpp {
    padding-top: 25px;

    .summary {
        padding: 0 1.899rem 1.899rem 1.899rem;

        .header {
            color: #26A69A;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .summary-info {
            font-size: 1rem;
            font-weight: 700;
            padding-top: 2rem;
        }

        .summary-link {
            cursor: pointer;
            font-weight: 700;
            color: #26a69a;
        }

        .qr-code {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 10px;
            position: relative;

            .actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
                align-items: center;
                justify-content: flex-end;
                height: 100%;
                padding-bottom: 5%;
                cursor: pointer;
            }
        }
    }

    .step {
        padding: 1.899rem;

        .header {
            color: #26A69A;
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }

        .image {
            .v-img {
                width: 100%;
                object-fit: cover;
                aspect-ratio: 1 / 1;
            }
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .image-item {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;

            .v-img {
                width: 100%;
                object-fit: cover;
                aspect-ratio: 1 / 1;
            }

            .download-wraper {
                position: absolute;
                background-color: #888888;
                width: 100%;
                height: 100%;
                text-align: center;
                display: none;
                justify-content: center;
                align-items: center;
                opacity: 0.9;
            }

            &:hover {
                .download-wraper {
                    display: flex;
                }
            }
        }
    }

}

.fullscreen-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 7rem;
}

.fullscreen-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.dpp-version {
    display: block;
    padding-right: 32px;
    font-size: 1.25rem;
    text-align: center;
}

.dpp-company-logo {
    margin-inline: 80%;
}

.additional-input-title {
    font-size: 1.2rem;
    color: #26A69A;
    margin-bottom: -10px;
}
</style>
