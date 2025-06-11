<script setup lang="ts">
import download from 'downloadjs'
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $listen } = useNuxtApp()
const isInputImagesModalOpen = ref(false)
const items = ref([])
const currentSlide = ref(0)

const currentImage = computed(() => items.value[currentSlide.value] ?? null)

$listen('openInputImagesModal', (imgsUrl: string[]) => {
    isInputImagesModalOpen.value = true
    items.value = imgsUrl
})

const closeInputImagesModal = () => {
    isInputImagesModalOpen.value = false
}

const handleKeydown = (event: KeyboardEvent) => {
    if (!isInputImagesModalOpen.value) {
        return
    }

    if (event.key === 'ArrowRight' && currentSlide.value < items.value.length - 1) {
        currentSlide.value += 1
    } else if (event.key === 'ArrowLeft' && currentSlide.value > 0) {
        currentSlide.value -= 1
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
})

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
        :is-open="isInputImagesModalOpen"
        name="input-images-modal"
        title=""
        button-submit-text="Save"
        class="input-images"
        @modal-close="closeInputImagesModal"
        @submit="submitHandler"
    >
        <template #content>
            <div>
                <div class="text-end">
                    <PhosphorIconDownloadSimple
                        size="24"
                        class="cursor-pointer"
                        @click="downloadFile(currentImage, currentImage.split('/').pop())"
                    />
                </div>
                <VCarousel
                    v-model="currentSlide"
                    hide-delimiters
                    show-arrows
                    tabindex="0"
                    height="400"
                >
                    <VCarouselItem
                        v-for="(item, i) in items"
                        :key="i"
                        :src="item"
                        cover
                    />
                </VCarousel>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                @click="closeInputImagesModal"
            >
                {{ $t('close') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.input-images.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        .modal-body {

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
