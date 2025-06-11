<script setup lang="ts">
import download from 'downloadjs'
import ModalLayout from '@/dialogs/modalLayout.vue'
import { formattedDate } from '@/helpers/formattedDate'
import InputImages from '~/dialogs/gallery/input-images.vue'
import { formatPascalCaseToLabel } from '@/helpers/textFormatter'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isHistoryInputModalOpen = ref(false)

const tableData = ref({
    headers: [
        { key: 'historyValue', title: t('historyInputs.tableHeader.historyValue') },
        { key: 'createdAt', title: t('historyInputs.tableHeader.updatedAt') },
        { key: 'updatedBy', title: t('historyInputs.tableHeader.updatedBy') },
    ],
    data: [],
})

const productInputIdLoc = ref()
const COUNT_SHOW_ITEMS = 5
const itemsToShow = ref(COUNT_SHOW_ITEMS)
const historyInputsStore = useHistoryInputsStore()
const productsInputsStore = useProductsInputsStore()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const urlDocument = ref<string>('')
const urlImage = ref<string>('')
const urlImages = ref<string>('')

const page = ref(1)
const itemsPerPage = 30
const totalItems = ref(0)

const actualProductInput = ref()
const historyEmpty = ref<boolean>(false)

$listen('openHistoryInputModal', async (productInputId: string) => {
    isHistoryInputModalOpen.value = true
    itemsToShow.value = COUNT_SHOW_ITEMS
    tableData.value.data = []

    actualProductInput.value = null
    historyEmpty.value = false

    const historyInputsResponse = await historyInputsStore.fetchHistoryInputs(page.value, itemsPerPage, productInputId)

    if (!historyInputsResponse?.length) {
        historyEmpty.value = true

        return
    }

    actualProductInput.value = await productsInputsStore.fetchProductInputById(productInputId)

    productInputIdLoc.value = productInputId

    const historyInputs = historyInputsResponse
        ?.filter((historyInput: any) => {
            if (historyInput.type === 'file' && !historyInput.document) {
                return false
            }

            if (historyInput.type === 'images' && historyInput.images?.length === 0) {
                return false
            }

            if (historyInput.type === 'image' && !historyInput.image) {
                return false
            }

            return true
        })
        .map((historyInput: any) => ({
            ...historyInput,
            createdAt: Number.isNaN(new Date(historyInput?.createdAt).getTime())
                ? '----'
                : formattedDate(historyInput?.createdAt),
            updatedBy: `${historyInput?.updatedBy.firstName} ${historyInput?.updatedBy.lastName}`,
        }))

    if (!historyInputs?.length) {
        historyEmpty.value = true

        return
    }

    tableData.value.data.push(...historyInputs)
    totalItems.value = historyInputsStore.totalItems

    urlImage.value = `${backendUrl.value}/media/product_input_images/`
    urlImages.value = `${backendUrl.value}/media/product_input_collection_images/`
    urlDocument.value = `${backendUrl.value}/media/product_input_documents/`
})

const visibleItems = computed(() => {
    return tableData.value.data.slice(0, itemsToShow.value)
})

const loadMore = async () => {
    if (itemsToShow.value < tableData.value.data.length) {
        itemsToShow.value += COUNT_SHOW_ITEMS
    } else if (tableData.value.data.length < totalItems.value) {
        page.value += 1

        const newData = await historyInputsStore.fetchHistoryInputs(page.value, itemsPerPage, productInputIdLoc.value)

        const formattedNewData = newData.map((historyInput: any) => ({
            ...historyInput,
            createdAt: formattedDate(historyInput?.createdAt),
            updatedBy: `${historyInput?.updatedBy.firstName} ${historyInput?.updatedBy.lastName}`,
        }))

        tableData.value.data.push(...formattedNewData)
        itemsToShow.value += COUNT_SHOW_ITEMS
    }
}

const closeHistoryInputModal = () => {
    isHistoryInputModalOpen.value = false
    actualProductInput.value = null
    historyEmpty.value = true
}

const isVideo = (filename: string) => {
    if (!filename) {
        return
    }

    const videoExtensions = ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm']
    const fileExtension = filename.split('.').pop()?.toLowerCase()

    return fileExtension && videoExtensions.includes(fileExtension)
}

const showImages = (images: []) => {
    if (!images?.length) {
        return
    }

    const imagesUrl = images.map((img: any) => {
        return urlImages.value + img.image
    })

    $event('openInputImagesModal', imagesUrl)
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
</script>

<template>
    <ModalLayout
        :is-open="isHistoryInputModalOpen"
        name="history-input-modal"
        :title="$t('historyInputs.title')"
        button-submit-text="Save"
        class="history-input"
        @modal-close="closeHistoryInputModal"
        @submit="submitHandler"
    >
        <template #content>
            <div
                v-if="visibleItems?.length"
                class="table-container"
            >
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th
                                v-for="(item, index) in tableData.headers"
                                :key="index"
                            >
                                {{ item.title }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(input, index) in visibleItems"
                            :key="index"
                        >
                            <td
                                v-for="(itemHeaders, indexHeaders) in tableData.headers"
                                :key="indexHeaders"
                            >
                                <div
                                    v-if="itemHeaders.key === 'historyValue'"
                                    class="d-flex align-center ga-3"
                                    style="justify-self: start;"
                                >
                                    <template v-if="input.type === 'file'">
                                        <div v-if="!input.measurementType">
                                            <div v-if="isVideo(input.document)">
                                                <video
                                                    width="200"
                                                    height="100"
                                                    controls
                                                >
                                                    <source
                                                        :src="urlDocument + input.document"
                                                        type="video/mp4"
                                                    >

                                                </video>
                                            </div>
                                            <PhosphorIconFileText
                                                v-else
                                                size="24"
                                                class="cursor-pointer"
                                                @click="downloadFile(urlDocument + input.document, input.document)"
                                            />
                                            <PhosphorIconDownloadSimple
                                                size="24"
                                                class="cursor-pointer"
                                                @click="downloadFile(urlDocument + actualProductInput.document, actualProductInput.document)"
                                            />
                                        </div>
                                        <div v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </div>
                                    </template>

                                    <div
                                        v-if="input.type === 'image'"
                                        class="img-wraper"
                                    >
                                        <div
                                            v-if="!input.measurementType && input?.image"
                                            class="download-wraper cursor-pointer"
                                        >
                                            <PhosphorIconDownloadSimple
                                                size="24"
                                                class="cursor-pointer"
                                                color="#fff"
                                                @click="downloadFile(urlImage + input.image, input.image)"
                                            />
                                        </div>
                                        <VImg
                                            v-if="!input.measurementType && input?.image"
                                            :src="urlImage + input.image"
                                            class="rounded-sm"
                                            style="width: 68px; height: 45px; object-fit: cover; flex-shrink: 0;"
                                        />
                                        <span v-else-if="!input?.image && input.measurementType">
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <template v-if="input.type === 'images'">
                                        <div
                                            v-if="!input.measurementType"
                                            class="position-relative"
                                        >
                                            <VImg
                                                v-if="input?.images?.length"
                                                :src="urlImages + input?.images[0].image"
                                                class="rounded-sm cursor-pointer"
                                                title="Show more"
                                                style="width: 68px; height: 45px; object-fit: cover; flex-shrink: 0;"
                                                @click="showImages(input?.images)"
                                            />
                                            <div
                                                class="position-absolute text-center w-100 cursor-pointer"
                                                title="Show more"
                                                @click="showImages(input?.images)"
                                            >
                                                <span v-if="input?.images?.length - 1 > 0">{{ `+ ${input?.images?.length - 1}` }}</span>
                                            </div>
                                        </div>
                                        <div v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </div>
                                    </template>
                                    <div v-if="input.type === 'dateTime'">
                                        <span
                                            v-if="!input.measurementType"
                                            class="font-weight-bold"
                                        >
                                            {{ formattedDate(input.dateTimeTo) }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <div v-if="input.type === 'dateTimeRange'">
                                        <span
                                            v-if="!input.measurementType"
                                            class="font-weight-bold"
                                        >
                                            {{ `${formattedDate(input.dateTimeTo)} => ${formattedDate(input.dateTimeFrom)}` }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <div v-if="input.type === 'coordinates'">
                                        <span
                                            v-if="!input.measurementType"
                                            class="font-weight-bold"
                                        >
                                            {{ `(lat) ${input.latitudeValue} (lng) ${input.longitudeValue}` }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <div v-if="input.type === 'text'">
                                        <span
                                            v-if="!input.measurementType"
                                            class="font-weight-bold"
                                        >
                                            {{ input.textValue }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <div v-if="input.type === 'numerical'">
                                        <span
                                            v-if="!input.measurementType"
                                            class="font-weight-bold"
                                        >
                                            {{ input.numericalValue }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <div v-if="input.type === 'textarea'">
                                        <span
                                            v-if="!input.measurementType"
                                            class="font-weight-bold"
                                        >
                                            {{ input.textAreaValue }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <div v-if="input.type === 'radioList'">
                                        <span
                                            v-if="!input.measurementType"
                                            class="font-weight-bold"
                                        >
                                            {{ input.radioValue }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <div v-if="input.type === 'checkboxList'">
                                        <span
                                            v-if="!input.measurementType"
                                            class="font-weight-bold"
                                        >
                                            {{ input.checkboxValue.join(', ') }}
                                        </span>
                                        <span v-else>
                                            {{
                                                `${formatPascalCaseToLabel(input.measurementType)} (${input.unitMeasurement}): ${input.measurementValue} ${input.unitSymbol}`
                                            }}
                                        </span>
                                    </div>

                                    <span class="font-weight-bold">{{ input[itemHeaders.key] }}</span>
                                </div>

                                <span v-else>{{ input[itemHeaders.key] }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                v-else
                class="text-center font-weight-bold"
            >
                <span v-if="historyEmpty">{{ $t('historyInputs.historyEmpty') }}</span>
                <span v-else>{{ $t('loding') }}</span>
            </div>

            <div
                v-if="actualProductInput"
                class="actual-value pa-6"
            >
                <span class="mb-2 d-block"> {{ $t('historyInputs.actualValue') }}: </span>

                <template v-if="actualProductInput.type === 'file'">
                    <div v-if="!actualProductInput.measurementType">
                        <div v-if="isVideo(actualProductInput.document)">
                            <video
                                width="300"
                                height="200"
                                controls
                            >
                                <source
                                    :src="urlDocument + actualProductInput.document"
                                    type="video/mp4"
                                >

                            </video>
                        </div>
                        <PhosphorIconFileText
                            v-else
                            size="24"
                            class="cursor-pointer"
                            @click="downloadFile(urlDocument + input.document, input.document)"
                        />
                        <PhosphorIconDownloadSimple
                            size="24"
                            class="cursor-pointer"
                            @click="downloadFile(urlDocument + actualProductInput.document, actualProductInput.document)"
                        />
                    </div>
                    <div v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </div>
                </template>

                <div
                    v-if="actualProductInput.type === 'image'"
                    class="img-wraper actual-img"
                >
                    <div
                        v-if="!actualProductInput.measurementType && actualProductInput?.image"
                        class="download-wraper cursor-pointer"
                    >
                        <PhosphorIconDownloadSimple
                            size="24"
                            class="cursor-pointer"
                            color="#fff"
                            @click="downloadFile(urlImage + actualProductInput.image, actualProductInput.image)"
                        />
                    </div>
                    <VImg
                        v-if="!actualProductInput.measurementType && actualProductInput?.image"
                        :src="urlImage + actualProductInput.image"
                        class="rounded-sm"
                        style="width: 180px; height: 100px; object-fit: cover; flex-shrink: 0;"
                    />
                    <span v-else-if="!actualProductInput?.image && actualProductInput.measurementType">
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <template v-if="actualProductInput.type === 'images'">
                    <div
                        v-if="!actualProductInput.measurementType"
                        class="position-relative"
                    >
                        <VImg
                            v-if="actualProductInput?.images?.length"
                            :src="urlImages + actualProductInput?.images[0].image"
                            class="rounded-sm cursor-pointer"
                            title="Show more"
                            style="width: 180px; height: 100px; object-fit: cover; flex-shrink: 0;"
                            @click="showImages(actualProductInput?.images)"
                        />
                        <div
                            class="position-absolute text-center w-33 cursor-pointer"
                            title="Show more"
                            @click="showImages(actualProductInput?.images)"
                        >
                            <span v-if="actualProductInput?.images?.length - 1 > 0">{{ `+ ${actualProductInput?.images?.length - 1}` }}</span>
                        </div>
                    </div>
                    <div v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </div>
                </template>

                <div v-if="actualProductInput.type === 'dateTime'">
                    <span
                        v-if="!actualProductInput.measurementType"
                        class="font-weight-bold"
                    >
                        {{ formattedDate(actualProductInput.dateTimeTo) }}
                    </span>
                    <span v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <div v-if="actualProductInput.type === 'dateTimeRange'">
                    <span
                        v-if="!actualProductInput.measurementType"
                        class="font-weight-bold"
                    >
                        {{ `${formattedDate(actualProductInput.dateTimeTo)} => ${formattedDate(actualProductInput.dateTimeFrom)}` }}
                    </span>
                    <span v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <div v-if="actualProductInput.type === 'coordinates'">
                    <span
                        v-if="!actualProductInput.measurementType"
                        class="font-weight-bold"
                    >
                        {{ `(lat) ${actualProductInput.latitudeValue} (lng) ${actualProductInput.longitudeValue}` }}
                    </span>
                    <span v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <div v-if="actualProductInput.type === 'text'">
                    <span
                        v-if="!actualProductInput.measurementType"
                        class="font-weight-bold"
                    >
                        {{ actualProductInput.textValue }}
                    </span>
                    <span v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <div v-if="actualProductInput.type === 'numerical'">
                    <span
                        v-if="!actualProductInput.measurementType"
                        class="font-weight-bold"
                    >
                        {{ actualProductInput.numericalValue }}
                    </span>
                    <span v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <div v-if="actualProductInput.type === 'textarea'">
                    <span
                        v-if="!actualProductInput.measurementType"
                        class="font-weight-bold"
                    >
                        {{ actualProductInput.textAreaValue }}
                    </span>
                    <span v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <div v-if="actualProductInput.type === 'radioList'">
                    <span
                        v-if="!actualProductInput.measurementType"
                        class="font-weight-bold"
                    >
                        {{ actualProductInput.radioValue }}
                    </span>
                    <span v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <div v-if="actualProductInput.type === 'checkboxList'">
                    <span
                        v-if="!actualProductInput.measurementType"
                        class="font-weight-bold"
                    >
                        {{ actualProductInput.checkboxValue.join(', ') }}
                    </span>
                    <span v-else>
                        {{
                            `${formatPascalCaseToLabel(actualProductInput.measurementType)} (${actualProductInput.unitMeasurement}): ${actualProductInput.measurementValue} ${actualProductInput.unitSymbol}`
                        }}
                    </span>
                </div>

                <span class="mb-2 d-block">{{ $t('historyInputs.updatedAt') }}:
                    <span class="font-weight-bold">
                        {{ formattedDate(actualProductInput.updatedAt) }}
                    </span>
                </span>
            </div>
        </template>

        <template #footer>
            <VBtn
                v-if="itemsToShow < totalItems && visibleItems?.length"
                variant="text"
                class="submit-btn"
                @click="loadMore"
            >
                {{ $t('loadMore') }}
            </VBtn>
            <div v-else />
        </template>
    </ModalLayout>

    <InputImages />
</template>

<style scoped lang="scss">
.history-input.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        .modal-body {
            .table-container {
                max-width: 600px;
                margin: 0 auto;

                .custom-table {
                    width: 100%;
                    border-collapse: collapse;
                    background-color: white;

                    thead tr {
                        background-color: #26a69a;
                        color: white;
                        text-align: left;
                    }

                    th, td {
                        padding: 12px;
                        font-size: 15px;
                    }

                    tbody {
                        tr {
                            border-bottom: 1px solid #e0e0e0;

                            td {
                                padding-block-start: 2rem;
                                padding-block-end: 2rem;
                            }
                        }
                    }
                }
            }

        }

        .img-wraper {
            position: relative;

            &.actual-img {
                width: 25%;
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
                z-index: 9;
            }

            &:hover {
                .download-wraper {
                    display: flex;
                }
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
}
</style>
