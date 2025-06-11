<script setup lang="ts">
import Map from "~/components/Map.vue"

definePageMeta({
    title: 'page.detailOfDpp.title',
    name: 'detail-of-logistics',
})

const route = useRoute()
const qrId = route.params.qrId

const logisticsStore = useLogisticsStore()
const logistics = ref({})

const urlDocument = ref<string>('')
const urlImage = ref<string>('')
const urlImages = ref<string>('')

const fullscreen = ref(false)
const activeStepIds = ref([])

const inputs = ref({})
const mapCoordinates = ref([])

const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

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

const formattedDate = (date: string) => {
    const newDate = date ? new Date(date) : '----'

    return newDate.toLocaleString()
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

const fetchLogistics = async () => {
    logistics.value = await logisticsStore.fetchLogisticsByQrId(qrId.toString())

    for (const step of logistics.value?.logisticsSteps) {
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

onMounted(async () => {
    await fetchLogistics()
    urlDocument.value = `${backendUrl.value}/media/product_input_documents/`
    urlImage.value = `${backendUrl.value}/media/product_input_images/`
    urlImages.value = `${backendUrl.value}/media/product_input_collection_images/`
})
</script>

<template>
    <VContainer
        v-if="logistics"
        fluid
        class="detail-dpp"
    >
        <span class="dpp-version">v1.00.01</span>
        <header>
            <h2>{{ logistics.name }}</h2>

            <div class="qr-code">
                <VImg
                    :src="`${backendUrl}/media/logistics_qrs/${logistics.qrImage}`"
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
                    {{ $t('dpps.createdBy') }}: {{ logistics?.userData?.firstName }} {{ logistics?.userData?.lastName }}
                </p>
                <p class="font-weight-bold">
                    {{ $t('dpps.createdAt') }}: {{ logistics?.createdAt }}
                </p>
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
                class="mt-15 px-8"
            >
                <VRow>
                    <VCol
                        v-if="inputGroup?.startingPointCoordinates"
                        cols="12"
                        lg="4"
                        md="6"
                    >
                        <p>Starting point (Coordinates):</p>
                        <div class="font-weight-bold">
                            starting point lat value: {{ inputGroup?.startingPointCoordinates[0]?.latitude }}<br>
                            starting point long value: {{ inputGroup?.startingPointCoordinates[0]?.longitude }}

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
                        <p>Destination point (Coordinates):</p>
                        <div class="font-weight-bold">
                            destination point lat value: {{ inputGroup?.destinationPointLat }}<br>
                            destination point long value: {{ inputGroup?.destinationPointLng }}

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
                        v-if="inputGroup?.company"
                        cols="12"
                        lg="4"
                        md="6"
                    >
                        <p>Company:</p>
                        <p class="font-weight-bold">
                            {{ inputGroup?.company?.name }}
                        </p>
                    </VCol>
                    <VCol
                        v-if="inputGroup?.arrivalTime"
                        cols="12"
                        lg="4"
                        md="6"
                    >
                        <p>Arrival time:</p>
                        <p class="font-weight-bold">
                            {{ formattedDate(inputGroup?.arrivalTime) }}
                        </p>
                    </VCol>
                    <VCol
                        v-if="inputGroup?.totalDistance"
                        cols="12"
                        lg="4"
                        md="6"
                    >
                        <p>Total distance made:</p>
                        <p class="font-weight-bold">
                            {{ inputGroup?.totalDistance }}
                        </p>
                    </VCol>
                    <VCol
                        v-if="inputGroup?.typeOfTransport"
                        cols="12"
                        lg="4"
                        md="6"
                    >
                        <p>Type of transport:</p>
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
                        <p>Departure time:</p>
                        <p class="font-weight-bold">
                            {{ formattedDate(inputGroup?.departureTime) }}
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
</template>

<style scoped lang="scss">
.detail-dpp {
    header {
        padding: 1.875rem;
        text-align: center;

        h2 {
            color: #26A69A;
            font-weight: 700;
            font-size: 30px;
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
