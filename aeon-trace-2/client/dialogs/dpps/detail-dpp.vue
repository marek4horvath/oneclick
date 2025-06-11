<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'
import { formattedDate } from '@/helpers/formattedDate'

const props = defineProps<{
    noChangeTitle: boolean
}>()

const { $event, $listen } = useNuxtApp()
const isDetailDppModalOpen = ref(false)
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const urlDocument = ref<string>('')
const urlImage = ref<string>('')
const urlImages = ref<string>('')

const router = useRouter()
const dppStore = useDppStore()
const nodeStore = useNodesStore()
const productsStore = useProductsStore()
const dpp = ref(null)

const inputs = ref({})
const mapCoordinates = ref([])
const node = ref([])
const nodes = ref([])

const fullscreen = ref(false)
const activeStepIds = ref([])
const qrCodePath = ref('')

const dppDetail = async (dppId: string, type: string) => {
    const nodesSet = new Set()

    if (type === 'dpp') {
        dpp.value = await dppStore.fetchDpp(dppId as string)
        qrCodePath.value = 'dpp_qrs'

        if (!props.noChangeTitle) {
            router.currentRoute.value.meta.title = dpp.value?.name || 'DPP'
        }

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

        node.value = [await nodeStore.fetchNode(dpp.value?.node['@id']?.split('/').pop() as string)]

        node.value = node.value.map((nodeItem: any) => {
            return {
                ...nodeItem,
                processColor: nodeItem.typeOfProcess.color,
                process: nodeItem.typeOfProcess?.name,
            }
        })

        nodes.value = Array.from(nodesSet)
    }

    if (type === 'product-step') {
        dpp.value = await productsStore.fetchProductStepById(dppId as string)
        qrCodePath.value = 'step_qrs'

        if (!props.noChangeTitle) {
            router.currentRoute.value.meta.title = dpp.value?.name || 'DPP'
        }

        inputs.value[dpp.value.id] = {
            step: dpp.value,
            inputs: dpp.value?.productInputs || [],
        }

        const coordinatesInputs = dpp.value?.productInputs.filter((input: any) => input.type === 'coordinates')

        if (coordinatesInputs.length > 0) {
            mapCoordinates.value = coordinatesInputs.map((input: any) => ({
                lat: input.latitudeValue,
                lng: input.longitudeValue,
            }))
        }

        node.value = [await nodeStore.fetchNode(dpp.value?.nodeId)]

        node.value = node.value.map((nodeItem: any) => {
            return {
                ...nodeItem,
                processColor: nodeItem.typeOfProcess.color,
                process: nodeItem.typeOfProcess?.name,
            }
        })

        nodes.value = Array.from(nodesSet)
    }
}

$listen('openDetailDppModal', async (data: any) => {
    isDetailDppModalOpen.value = true

    if (!data.id || !data.type) {
        return
    }

    await dppDetail(data.id, data.type)
})

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

    link.href = `${backendUrl.value}/media/dpp_qrs/${dpp.value.qrImage}`
    link.download = 'qr-code.png'
    link.click()
}

const closeHandler = () => {
    $event('closeDetailDppModal', isDetailDppModalOpen.value)
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

const isVideo = (filename: string) => {
    const videoExtensions = ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm']
    const fileExtension = filename.split('.').pop()?.toLowerCase()

    return fileExtension && videoExtensions.includes(fileExtension)
}

const downloadFile = (fileUrl: string, filename: string) => {
    const link = document.createElement('a')

    link.href = fileUrl
    link.download = filename
    link.click()
}

const closeDetailProcessModal = () => {
    isDetailDppModalOpen.value = false
    dpp.value = null
    nodes.value = []
    activeStepIds.value = []
    node.value = []
    inputs.value = {}
    closeHandler()
}
</script>

<template>
    <ModalLayout
        :is-open="isDetailDppModalOpen"
        name="detail-dpp-modal"
        :title="$t('dpps.detail')"
        width="70vw"
        button-submit-text="Save"
        class="detail-dpp"
        @modal-close="closeDetailProcessModal"
        @submit="submitHandler"
    >
        <template #content>
            <VContainer
                fluid
                class="detail-dpp"
            >
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
                                    {{ $t('dpps.createdAt') }}: {{ dpp?.createdAt }}
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
                                    </div>
                                    <VImg
                                        :src="`${backendUrl}/media/${qrCodePath}/${dpp.qrImage}`"
                                        height="162"
                                        width="162"
                                        max-height="162"
                                        max-width="162"
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
                        class="mt-15"
                    >
                        <VRow>
                            <VCol
                                v-for="input in inputGroup.inputs"
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

                <VCard
                    variant="flat"
                    class="mt-4"
                >
                    <TreeFlow
                        :data="node"
                        connection-key="none"
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
                    :src="`${backendUrl}/media/dpp_qrs/${dpp.qrImage}`"
                    class="fullscreen-image"
                />
            </div>

            <PrintTemplate
                v-if="dpp"
                :qr-src="`${backendUrl}/media/dpp_qrs/${dpp.qrImage}`"
                class="hidden"
            />
        </template>

        <template #footer>
            <div />
        </template>
    </ModalLayout>
</template>

<style lang="scss" scoped>
.detail-dpp {

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
