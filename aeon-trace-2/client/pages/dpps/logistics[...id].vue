<script setup lang="ts">
import { nextTick } from 'vue'
import Map from "~/components/Map.vue"
import type { SelectItem } from '@/types/selectItem'
import { formattedDate } from '@/helpers/formattedDate'
import DetailDpp from '~/dialogs/dpps/detail-dpp.vue'

definePageMeta({
    title: 'logistics.logistics',
    name: 'logistics-detail',
    layout: 'dashboard',
    middleware: 'auth',
})

const { $event, $listen } = useNuxtApp()
const route = useRoute()
const router = useRouter()
const nodeStore = useNodesStore()
const logisticsStore = useLogisticsStore()
const productsStore = useProductsStore()
const dppStore = useDppStore()
const { t } = useI18n()
const fullscreen = ref(false)
const activeStepIds = ref([])

const urlDocument = ref<string>('')
const urlImage = ref<string>('')
const urlImages = ref<string>('')

const nodes = ref([])
const inputs = ref({})
const mapCoordinates = ref([])

const dppItems = ref<SelectItem[]>([])
const selectedDpp = ref(null)

const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const logistics = ref<any>({})
const logisticsId = ref<string>('')
const dppType = ref()

const countLogistics = ref({
    assignedToDppData: [],
    exportLogisticsData: [],
    assignedToDpp: 0,
    exportLogistics: 0,
})

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
        { color: '#FF007F', name: t('dppDetail.inUseLogistics') },
    ],
})

const isVideo = (filename: string) => {
    const videoExtensions = ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm']
    const fileExtension = filename.split('.').pop()?.toLowerCase()

    return fileExtension && videoExtensions.includes(fileExtension)
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

const printQr = () => {
    const printWindow = window.open('', '', 'height=600,width=800')
    const content = document.getElementById('printable-content').innerHTML

    printWindow.document.write('<html><head><title>Print</title></head><body >')
    printWindow.document.write(content)
    printWindow.document.write('</body></html>')
    printWindow.document.close()
    printWindow.focus()
    printWindow.print()
}

const downloadQr = () => {
    const link = document.createElement('a')

    link.href = `${backendUrl.value}/media/logistics_qrs/${logistics.value?.qrImage}`
    link.download = 'qr-code.png'
    link.click()
}

const downloadFile = (fileUrl: string, filename: string) => {
    const link = document.createElement('a')

    link.href = fileUrl
    link.download = filename
    link.click()
}

const openFullscreen = () => {
    fullscreen.value = true
}

const closeFullscreen = () => {
    fullscreen.value = false
}

const findOccurrences = (data, searchId) => {
    const results = {}

    Object.entries(data).forEach(([key, section]) => {
        let count = 0
        if (Array.isArray(section.data)) {
            count = section.data.filter(item => item["@id"].includes(searchId)).length
        } else if (typeof section.data === 'object') {
            count = Object.values(section.data).filter(item => item["@id"].includes(searchId)).length
        }

        if (count > 0) {
            results[key] = (results[key] || 0) + count
        }
    })

    return Object.keys(results).length > 0 ? results : null
}

const fetchLogisticsDetail = async () => {
    logistics.value = await logisticsStore.fetchLogisticsById(logisticsId.value)
    router.currentRoute.value.meta.title = logistics.value?.name || 'logistics.logistics'

    if (logistics.value.fromDpps?.length > 0 || logistics.value.toDpps?.length > 0) {
        logistics.value.fromDpps.forEach(async (fromDpp: any) => {
            const fromDppId = fromDpp.split('/').pop()
            const fromDppData = await dppStore.fetchDpp(fromDppId as string)

            dppItems.value.push({
                value: fromDppData.id,
                title: `${fromDppData.id} - ${fromDppData.name}`,
            })
        })

        logistics.value.toDpps.forEach(async (toDpp: any) => {
            const toDppId = toDpp.split('/').pop()
            const toDppData = await dppStore.fetchDpp(toDppId as string)

            dppItems.value.push({
                value: toDppData.id,
                title: `${toDppData.id} - ${toDppData.name}`,
            })
        })

        dppType.value = 'dpp'
    }

    if (logistics.value.fromProductSteps?.length > 0 || logistics.value.toProductSteps?.length > 0) {
        logistics.value.fromProductSteps.forEach(async (fromProductSteps: any) => {
            const fromProductStepsId = fromProductSteps.split('/').pop()
            const fromProductStepsData = await productsStore.fetchProductStepById(fromProductStepsId as string)

            dppItems.value.push({
                value: fromProductStepsData.id,
                title: `${fromProductStepsData.id} - ${fromProductStepsData.name}`,
            })
        })

        logistics.value.toProductSteps.forEach(async (toProductSteps: any) => {
            const toProductStepsId = toProductSteps.split('/').pop()
            const toProductStepsData = await productsStore.fetchProductStepById(toProductStepsId as string)

            dppItems.value.push({
                value: toProductStepsData.id,
                title: `${toProductStepsData.id} - ${toProductStepsData.name}`,
            })
        })

        dppType.value = 'product-step'
    }

    const nodeIds = [
        logistics.value?.fromNode?.split('/').pop(),
        logistics.value?.toNode?.split('/').pop(),
    ].filter(Boolean)

    const nodeObjects = await Promise.all(nodeIds.map(id => nodeStore.fetchNode(id)))

    nodes.value = nodeObjects.map((nodeItem: any) => {
        let childId = null

        const resultsFromDpp = logistics.value.fromDpps.reduce((acc, fromDpp: any) => {
            const occurrences = findOccurrences(nodeItem.countDppData, fromDpp.split('/').pop())

            if (occurrences) {
                Object.entries(occurrences).forEach(([key, value]) => {
                    acc[key] = (acc[key] || 0) + value
                })
            }

            return acc
        }, {})

        const resultsToDpp = logistics.value.toDpps.reduce((acc, toDpp: any) => {
            const occurrences = findOccurrences(nodeItem.countDppData, toDpp.split('/').pop())

            if (occurrences) {
                Object.entries(occurrences).forEach(([key, value]) => {
                    acc[key] = (acc[key] || 0) + value
                })
            }

            return acc
        }, {})

        nodeItem.countDpp = {
            dppInUse: (resultsFromDpp?.dppInUse > 0 ? 1 : 0) || (resultsToDpp?.dppInUse > 0 ? 1 : 0) || 0,
            logistics: (resultsFromDpp?.logistics > 0 ? 1 : 0) || (resultsToDpp?.logistics > 0 ? 1 : 0) || 0,
            exportDpp: (resultsFromDpp?.exportDpp > 0 ? 1 : 0) || (resultsToDpp?.exportDpp > 0 ? 1 : 0) || 0,
        }

        if (logistics.value.state === 'ASSIGNED_TO_DPP') {
            nodeItem.countLogistics = {
                assignedToDpp: nodeItem.countLogistics.assignedToDpp > 0 ? 1 : 0,
                exportLogistics: 0,
                exportLogisticsData: [],
                assignedToDppData: nodeItem.countLogistics.assignedToDppData,
                inUseLogistics: 0,
                inUseLogisticsData: [],
            }
        }

        if (logistics.value.state === 'EXPORT_LOGISTICS') {
            nodeItem.countLogistics = {
                assignedToDpp: 0,
                exportLogistics: nodeItem.countLogistics.exportLogistics > 0 ? 1 : 0,
                exportLogisticsData: nodeItem.countLogistics.exportLogisticsData,
                assignedToDppData: [],
                inUseLogistics: 0,
                inUseLogisticsData: [],
            }
        }

        if (logistics.value.state === 'IN_USE_LOGISTICS') {
            nodeItem.countLogistics = {
                assignedToDpp: 0,
                inUseLogistics: nodeItem.countLogistics.inUseLogistics > 0 ? 1 : 0,
                inUseLogisticsData: nodeItem.countLogistics.inUseLogisticsData,
                assignedToDppData: [],
                exportLogistics: 0,
                exportLogisticsData: [],
            }
        }

        if (!nodeItem.children?.length) {
            countLogistics.value = nodeItem.countLogistics
        }

        if (nodeItem.children?.length) {
            countLogistics.value = nodeItem.countLogistics
            childId = nodeItem.children[0].id
        }

        if (childId) {
            nextTick(() => {
                const nodeToUpdate = nodes.value.find((node: any) => node.id === nodeItem.id)

                if (nodeToUpdate) {
                    nodeToUpdate.countLogistics = countLogistics.value
                }
            })
        }

        return {
            ...nodeItem,
            processColor: nodeItem.typeOfProcess?.color,
            process: nodeItem.typeOfProcess?.name,
        }
    })

    for (const step of logistics.value?.logisticsSteps) {
        if (step.state === 'EXPORT_LOGISTICS') {
            nodes.value[1].countLogistics.exportLogisticsData = nodes.value[1].countLogistics.exportLogisticsData.filter(item => {
                return item["@id"].split('/').pop() === step.id
            })
        }

        if (step.state === 'ASSIGNED_TO_DPP') {
            nodes.value[1].countLogistics.assignedToDppData = nodes.value[1].countLogistics.assignedToDppData.filter(item => {
                return item["@id"].split('/').pop() === step.id
            })
        }
        if (step.productInputs.length === 0) {
            continue
        }
        if (!inputs.value[step.id]) {
            inputs.value[step.id] = {
                step,
                inputs: step.productInputs,
            }
        }

        const coordinatesInputs = step.productInputs.filter((input: any) => input.type === 'coordinates')

        if (coordinatesInputs.length > 0) {
            mapCoordinates.value = coordinatesInputs.map((input: any) => ({
                lat: input.latitudeValue,
                lng: input.longitudeValue,
            }))
        }
    }
}

$listen('closeDetailDppModal', (isOpen: boolean) => {
    if (!isOpen) {
        selectedDpp.value = null
    }
})

watch(selectedDpp, newValue => {
    if (!newValue) {
        return
    }

    $event('openDetailDppModal', { id: newValue, type: dppType.value })
})

onMounted(async () => {
    logisticsId.value = route.params.id[1]
    urlDocument.value = `${backendUrl.value}/media/product_input_documents/`
    urlImage.value = `${backendUrl.value}/media/product_input_images/`
    urlImages.value = `${backendUrl.value}/media/product_input_collection_images/`

    await fetchLogisticsDetail()
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer
            fluid
            class="detail-logistics"
        >
            <div
                v-if="logistics"
                class="mb-5 summary"
            >
                <div>
                    <VSelect
                        v-model="selectedDpp"
                        :items="dppItems"
                        class="w-33"
                        :label="$t('dpps.assignedDpps')"
                        :placeholder="$t('dpps.assignedDpps')"
                        variant="outlined"
                    />
                </div>

                <div class="header">
                    {{ $t('dpps.summary') }}
                </div>

                <div>
                    <VRow>
                        <VCol class="summary-info">
                            <p class="font-weight-bold">
                                {{ $t('dpps.name') }}:  {{ logistics?.name }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.uidd') }}:  {{ logistics?.id }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.company') }}: {{ logistics?.logisticsSteps?.[0]?.company.name || '' }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.createdBy') }}: {{ logistics?.userData?.firstName }} {{ logistics?.userData?.lastName }}
                            </p>
                            <p class="font-weight-bold">
                                {{ $t('dpps.createdAt') }}: {{ formattedDate(logistics?.createdAt) }}
                            </p>
                        </VCol>

                        <VCol>
                            <div
                                v-if="logistics.qrImage"
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
                                </div>
                                <VImg
                                    :src="`${backendUrl}/media/logistics_qrs/${logistics.qrImage}`"
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
                v-for="inputGroup in logistics?.logisticsSteps"
                :key="inputGroup.id"
                class="mb-4 rounded-lg step"
            >
                <VCardTitle
                    class="header"
                    :style="isActive(inputGroup?.id) ? 'border-bottom: 1px solid;' : 'border-bottom: unset'"
                    @click="toggleAccordion(inputGroup?.id)"
                >
                    <template v-if="inputGroup">
                        <span style="width: 80%; display: block;">{{ $t('dpps.inputs') }} {{ $t('dppDetail.forStep', { name: inputGroup?.name }) }}</span>
                        <PhosphorIconCaretDown v-if="!isActive(inputGroup?.id)" />
                        <LazyPhosphorIconCaretUp v-if="isActive(inputGroup?.id)" />
                    </template>
                </VCardTitle>
                <VCardText
                    v-show="isActive(inputGroup?.id)"
                    class="mt-15"
                >
                    <VRow>
                        <VCol
                            v-if="inputGroup?.company"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <p> {{ $t('logistics.logisticsCompany') }}:</p>
                            <p class="font-weight-bold">
                                {{ inputGroup?.company?.name }}
                            </p>
                        </VCol>
                        <VCol
                            v-if="inputGroup?.typeOfTransport"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <p>{{ $t('logisticsTemplate.typeOfTransport') }}:</p>
                            <p class="font-weight-bold">
                                {{ inputGroup?.typeOfTransport }}
                            </p>
                        </VCol>
                        <VCol
                            v-if="inputGroup?.departureTime"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <p>{{ $t('logisticsTemplate.departureTime') }}:</p>
                            <p class="font-weight-bold">
                                {{ formattedDate(inputGroup?.departureTime) }}
                            </p>
                        </VCol>
                        <VCol
                            v-if="inputGroup?.arrivalTime"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <p>{{ $t('logisticsTemplate.arrivalTime') }}:</p>
                            <p class="font-weight-bold">
                                {{ formattedDate(inputGroup?.arrivalTime) }}
                            </p>
                        </VCol>
                        <VCol
                            v-if="inputGroup?.startingPointCoordinates"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <p>{{ $t('logisticsTemplate.startingPointCoordinates') }}:</p>
                            <div class="font-weight-bold">
                                <br>
                                <p v-if="inputGroup?.startingCompanyName">
                                    {{ $t('logistics.startingCompanyName') }}:
                                    {{ inputGroup?.startingCompanyName }}
                                </p>
                                {{ $t('logisticsTemplate.startingPointLat') }}: {{ inputGroup?.startingPointCoordinates[0]?.latitude }}<br>
                                {{ $t('logisticsTemplate.startingPointLong') }}: {{ inputGroup?.startingPointCoordinates[0]?.longitude }}

                                <VCard
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
                                                    lat: inputGroup?.startingPointCoordinates[0]?.latitude,
                                                    lng: inputGroup?.startingPointCoordinates[0]?.longitude,
                                                }],
                                                color: 'blue',
                                                connectLine: false,
                                            },
                                        ]"
                                    />
                                </VCard>
                            </div>
                        </VCol>
                        <VCol
                            v-if="inputGroup?.destinationPointLat && inputGroup?.destinationPointLng"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <p>{{ $t('logisticsTemplate.destinationPointCoordinates') }}:</p>
                            <div class="font-weight-bold">
                                <br>
                                <p v-if="inputGroup?.destinationCompanyName">
                                    {{ $t('logistics.destinationCompanyName') }}:
                                    {{ inputGroup?.destinationCompanyName }}
                                </p>
                                {{ $t('logisticsTemplate.destinationPointLat') }}: {{ inputGroup?.destinationPointLat }}<br>
                                {{ $t('logisticsTemplate.destinationPointLong') }}: {{ inputGroup?.destinationPointLng }}

                                <VCard
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
                                                    lat: inputGroup?.destinationPointLat,
                                                    lng: inputGroup?.destinationPointLng,
                                                }],
                                                color: 'blue',
                                                connectLine: false,
                                            },
                                        ]"
                                    />
                                </VCard>
                            </div>
                        </VCol>
                        <VCol
                            v-if="inputGroup?.totalDistance"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <p> {{ $t('logisticsTemplate.totalDistanceMade') }}:</p>
                            <p class="font-weight-bold">
                                {{ inputGroup?.totalDistance }}
                            </p>
                        </VCol>
                        <VCol
                            v-for="input in inputGroup.productInputs"
                            :key="input.id"
                            cols="12"
                            lg="4"
                            md="6"
                        >
                            <p>{{ input.name }}:</p>
                            <p class="font-weight-bold">
                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'numerical'">
                                    {{ input.numericalValue }}
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'text'">
                                    {{ input.textValue }}
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'file'">
                                    <PhosphorIconDownloadSimple
                                        v-if="input.document"
                                        size="24"
                                        class="cursor-pointer"
                                        @click="downloadFile(`/media/product_input_documents/${input.document}`, input.document)"
                                    />
                                    <div v-if="input.document">
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
                                            <p class="text-center">
                                                <PhosphorIconFileText size="100" />
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'textarea'">
                                    {{ input.textAreaValue }}
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'datetime'">
                                    <p>
                                        {{ $t('dpps.dateTimeFrom') }} : {{ formattedDate(input.dateTimeFrom) }}
                                    </p>
                                    <p>
                                        {{ $t('dpps.dateTimeTo') }}  : {{ formattedDate(input.dateTimeTo) }}
                                    </p>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'image'">
                                    <div class="image">
                                        <VImg
                                            :src="urlImage + input.image"
                                            class="w-100 mx-auto"
                                        />
                                    </div>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'images'">
                                    <div class="image-grid">
                                        <div
                                            v-for="image in input.images"
                                            :key="image.id"
                                            class="image-item"
                                        >
                                            <VImg :src="urlImages + image.image" />
                                        </div>
                                    </div>
                                </template>

                                <template v-if="input.type.toLowerCase().replace(/\s+/g, '') === 'coordinates'">
                                    {{ $t('address.latitude') }}: {{ input.latitudeValue }}<br>
                                    {{ $t('address.longitude') }}: {{ input.longitudeValue }}

                                    <VCard
                                        v-if="mapCoordinates.length > 0"
                                        class="map"
                                        variant="flat"
                                    >
                                        <Map
                                            :is-active-map="false"
                                            :is-marker-clicked="false"
                                            :zoom="5"
                                            :address-groups="[
                                                {
                                                    addresses: mapCoordinates,
                                                    color: 'blue',
                                                    connectLine: false,
                                                },
                                            ]"
                                        />
                                    </VCard>
                                </template>
                            </p>
                        </VCol>
                    </VRow>
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
                    :data="nodes"
                    connection-key="parents"
                    traversal="forward"
                    disable-options
                />
            </VCard>
        </VContainer>

        <div
            v-if="fullscreen"
            class="fullscreen-overlay"
            @click="closeFullscreen"
        >
            <VImg
                :src="`${backendUrl}/media/logistics_qrs/${logistics.qrImage}`"
                class="fullscreen-image"
            />
        </div>

        <PrintTemplate
            v-if="logistics"
            :qr-src="`${backendUrl}/media/logistics_qrs/${logistics.qrImage}`"
            class="hidden"
        />
    </NuxtLayout>
    <DetailDpp no-change-title />
</template>

<style lang="scss" scoped>
.summary {
    padding: 1.899rem;

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
        white-space: unset;
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

        .v-img {
            width: 100%;
            object-fit: cover;
            aspect-ratio: 1 / 1;
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
</style>
