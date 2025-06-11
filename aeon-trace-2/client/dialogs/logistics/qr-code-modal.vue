<script lang="ts" setup>
import ModalLayout from '@/dialogs/modalLayout.vue'
import { PhosphorIconDownloadSimple, PhosphorIconQrCode } from "#components"

const { $listen } = useNuxtApp()
const isQrCodeModalOpen = ref(false)
const qrCode = ref('')
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const dppName = ref('')
const qrIdRef = ref('')

$listen('openQrCodeModal', ({ qrId, code, name, typeDpp }: { qrId: string; code: string; name?: string; typeDpp?: string }) => {
    isQrCodeModalOpen.value = true

    if (!code) {
        return
    }

    dppName.value = name
    qrIdRef.value = qrId

    if (typeDpp === 'LOGISTICS_DPP') {
        qrCode.value = `${backendUrl.value}/media/logistics_qrs/${code}`
    }

    if (typeDpp === 'DPP') {
        qrCode.value = `${backendUrl.value}/media/dpp_qrs/${code}`
    }

    if (typeDpp === 'STEP_DPP') {
        qrCode.value = `${backendUrl.value}/media/step_qrs/${code}`
    }
})

function sanitizeFilename(name: string): string {
    return name.trim().replace(/[<>:"/\\|?*]+/g, '-')
}

const handleDownloadPNG = () => {
    const imgElement = document.querySelector('.qr-code img') as HTMLImageElement

    if (!imgElement) {
        console.error('Image element not found')

        return
    }

    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')

    canvas.width = imgElement.naturalWidth
    canvas.height = imgElement.naturalHeight

    context?.drawImage(imgElement, 0, 0, canvas.width, canvas.height)

    const safeName = sanitizeFilename(qrIdRef.value)

    try {
        canvas.toBlob(blob => {
            if (!blob) {
                console.error('Failed to create blob')

                return
            }

            const imageURL = URL.createObjectURL(blob)

            const link = document.createElement('a')

            link.href = imageURL
            link.download = `${safeName}.png`

            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
        }, 'image/png')
    } catch (error) {
        console.error('Failed to create blob', error)

        window.open(qrCode.value, '_blank')
    }
}

const closeQrCodeModal = (isDownload: boolean = false) => {
    if (isDownload) {
        handleDownloadPNG()
    }

    isQrCodeModalOpen.value = false
    qrCode.value = ''
    dppName.value = ''
}

const printHandler = () => {
    const localWindow = window.open()

    if (!localWindow) {
        console.error('Failed to open window')

        return
    }

    localWindow.document.write(document.querySelector('.qr-code').innerHTML)
    localWindow.print()
    localWindow.close()
}
</script>

<template>
    <ModalLayout
        :is-open="isQrCodeModalOpen"
        width="400px"
        name="qr-code-modal"
        :title="dppName ? `QR code DPP: ${dppName}` : 'Qr Code'"
        @modal-close="closeQrCodeModal"
    >
        <template #content>
            <div class="qr-code">
                <VImg
                    v-if="qrCode"
                    :src="qrCode"
                    height="250"
                    max-height="250"
                    max-width="250"
                />
                <span v-else>
                    {{ $t('logisticsTemplate.noQrCode') }}
                </span>
            </div>
        </template>
        <template #footer>
            <div
                v-if="qrCode"
                class="qr-buttons"
                :class="`test =>  ${qrCode}`"
            >
                <VBtn
                    variant="flat"
                    rounded="0"
                    size="large"
                    class="submit-btn qr-button-w-100"
                    :prepend-icon="PhosphorIconQrCode"
                    @click.stop="printHandler"
                >
                    {{ $t('printQr') }}
                </VBtn>

                <VBtn
                    variant="text"
                    rounded="0"
                    size="large"
                    class="cancel-btn qr-button-w-100"
                    :prepend-icon="PhosphorIconDownloadSimple"
                    @click.stop="closeQrCodeModal(true)"
                >
                    {{ $t('download') }}
                </VBtn>
            </div>
            <div v-else />
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.qr-code-modal-width {
    max-width: 350px !important;
}
.qr-code {
    z-index: 1005;
    display: flex;
    justify-content: center;
    align-items: center;
}

.qr-buttons {
    width: 100%;
    .v-btn {
        padding-inline: 1rem;
        padding-block: 0.5rem;
        background-color: rgba(38, 166, 154, 1);
        color: #FFFFFF;
        border-radius: 3px !important;
        margin-bottom: 10px;
    }

    .qr-button-w-100 {
        width: 100% !important;
    }
}
</style>
