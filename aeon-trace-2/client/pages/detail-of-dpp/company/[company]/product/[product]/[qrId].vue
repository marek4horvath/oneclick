<script setup lang="ts">
import download from 'downloadjs'
import { formattedDate } from '@/helpers/formattedDate'
import { formatPascalCaseToLabel } from '@/helpers/textFormatter'
import HistoryInput from '~/dialogs/dpps/history-input.vue'
import Map from '~/components/Map.vue'

definePageMeta({
    title: 'page.detailOfDpp.title',
    name: 'detail-of-dpp',
})

const { $event } = useNuxtApp()
const route = useRoute()

const companySlug = route.params.company
const qrId = route.params.qrId
const dppStore = useDppStore()

const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const urlDocument = ref<string>('')
const urlImage = ref<string>('')
const urlImages = ref<string>('')
const dpp = ref(null)
const inputs = ref({})
const mapCoordinates = ref([])
const fullscreen = ref(false)
const activeStepIds = ref([])

const pairs = ref<Record<string, any>>({})

const fetchTraceData = async () => {
    const id = dpp.value.id

    try {
        const traceData = await dppStore.fetchDppMaterialsTrace(id)
        if (traceData) {
            pairs.value = Object.values(traceData)
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

const fetchDpp = async () => {
    dpp.value = await dppStore.fetchDppByQrId(companySlug, qrId)

    for (const step of dpp.value?.productSteps) {
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

const openFullscreen = () => {
    fullscreen.value = true
}

const closeFullscreen = () => {
    fullscreen.value = false
}

const downloadQr = () => {
    const link = document.createElement('a')

    link.href = `${backendUrl.value}/media/dpp_qrs/${dpp.value.qrImage}`
    link.download = 'qr-code.png'
    link.click()
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

onMounted(async () => {
    urlDocument.value = `${backendUrl.value}/media/product_input_documents/`
    urlImage.value = `${backendUrl.value}/media/product_input_images/`
    urlImages.value = `${backendUrl.value}/media/product_input_collection_images/`

    await fetchDpp()
    await fetchTraceData()
})

const history = (productInputId: string) => {
    $event('openHistoryInputModal', productInputId)
}
</script>

<template>
    <VContainer
        v-if="dpp"
        fluid
        class="detail-dpp"
    >
        <span class="dpp-version">v1.00.01</span>
        <header>
            <h2>{{ dpp.name }}</h2>

            <div class="dpp-company-logo">
                <VImg
                    v-if="productStep?.company?.companyLogo"
                    :src="`${backendUrl}/media/company_logos/${productStep?.company?.companyLogo}`"
                    height="170"
                    max-height="170"
                    class="company-logo"
                />
            </div>

            <div class="qr-code">
                <VImg
                    :src="`${backendUrl}/media/dpp_qrs/${dpp.qrImage}`"
                    height="298"
                    max-height="298"
                    max-width="298"
                    @click="openFullscreen"
                />
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
            </div>
        </header>

        <div class="mb-5 summary">
            <div class="header">
                <h4>{{ $t('dpps.summary') }}</h4>
            </div>
            <div>
                <p class="font-weight-bold">
                    {{ $t('dpps.company') }}: {{ dpp?.company?.name }}
                </p>
                <p class="font-weight-bold">
                    {{ $t('dpps.createdBy') }}: {{ dpp?.userData?.firstName }} {{ dpp?.userData?.lastName }}
                </p>
                <p class="font-weight-bold">
                    {{ $t('dpps.createdAt') }}: {{ dpp?.createdAt }}
                </p>
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
                class="mt-15 px-8"
            >
                <VRow>
                    <VCol
                        v-if="inputGroup.step.measurementType !== 'batchQuantity' && inputGroup.step.measurementValue !== 0"
                        cols="12"
                        lg="4"
                        md="6"
                    >
                        {{ `${formatPascalCaseToLabel(inputGroup.step.measurementType)} (${inputGroup.step.unitMeasurement}): ${inputGroup.step.measurementValue} ${inputGroup.step.unitSymbol}` }}
                    </VCol>
                    <VCol
                        v-for="input in inputGroup.inputs"
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
                                <PhosphorIconClockCounterClockwise
                                    v-if="input.updatable && input.history?.length"
                                    :size="24"
                                    class="cursor-pointer"
                                    color="#888888"
                                    @click="history(input.id)"
                                />

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
                        <p class="font-weight-bold inputs">
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
            </VCardText>
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
            :src="`${backendUrl}/media/dpp_qrs/${dpp.qrImage}`"
            class="fullscreen-image"
        />
    </div>

    <PrintTemplate
        v-if="dpp"
        :qr-src="`${backendUrl}/media/dpp_qrs/${dpp.qrImage}`"
        class="hidden"
    />
    <HistoryInput />
</template>

<style lang="scss">
.detail-dpp {
    header {
        padding: 1.875rem;
        text-align: center;

        h2 {
            color: #26A69A;
            font-weight: 700;
            font-size: 30px;
        }

        .dpp-company-logo {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 10px;
            position: relative;
            .company-logo {
                width: 85%;
                margin-right: 15px;
            }
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

    .summary {
        padding-inline: 1rem;
        .header {
            h4 {
                color: #26A69A;
                font-weight: 700;
                font-size: 1.25rem;
                margin-block-end: 0.5rem;
            }
        }
    }

    .step {
        padding-block-end: 1rem;

        .header {
            padding-top: 1.5rem;
            padding-inline: 0;
            margin-inline: 1.9rem;
            color: #26A69A;
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .inputs {
            .map {
                margin-block-start: 1rem;
            }

            .image-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
                max-height: 300px;
                overflow-y: auto;
                padding-right: 10px;

                .image-item {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    position: relative;

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
    z-index: 999;
}

.fullscreen-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.dpp-version {
    display: flex;
    position: absolute;
    right: 0;
    padding-right: 32px;
    font-size: 1.25rem;
}
</style>
