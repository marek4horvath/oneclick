<script setup lang="ts">
import type { Nullable } from '@antfu/utils'

// Definovanie props a emitov
const props = defineProps({
    mode: {
        type: String,
        required: true,
        validator: (value: string) => ['single', 'multiple', 'file'].includes(value),
    },
    placeholder: {
        type: String,
        default: 'UPLOAD',
    },
    showFile: {
        type: Boolean,
        default: false,
    },
    showInstructions: {
        type: Boolean,
        default: false,
    },
    classBtn: {
        type: String,
        default: '',
    },
    image: {
        type: String,
        default: '',
    },
    isDisabled: {
        type: Boolean,
        default: false,
        required: false,
    },
})

const emit = defineEmits(['fileUploaded', 'imageUploaded', 'imageClicked'])

// Reactive pre uchovávanie údajov o obrázkoch a súboroch
const imageUrl = ref<Nullable<string>>(props.image)
const imageUrls = ref<Array<string>>([])
const file = ref(false)
const message = ref('')

const fileInput = ref<HTMLInputElement | null>(null)
const { t } = useI18n()

if (imageUrl.value?.length > 0) {
    imageUrls.value = imageUrl.value
}

const handleSingleFile = (fileData: File) => {
    if (fileData.size <= import.meta.env.VITE_APP_MAX_FILE_SIZE && ['image/jpeg', 'image/png', 'image/gif', 'image/heic', 'image/heif'].includes(fileData.type)) {
        const reader = new FileReader()

        reader.onload = e => {
            imageUrl.value = e.target?.result as string
            emit('imageUploaded', { url: imageUrl.value, file: fileData })
        }
        reader.readAsDataURL(fileData)
    } else {
        message.value = t('fileValid')
    }
}

const fileLength = ref(0)

const handleMultipleFiles = (fileData: FileList) => {
    const images: { url: string; file: File }[] = []

    fileLength.value = fileData.length

    Array.from(fileData).forEach(data => {
        if (data.size <= import.meta.env.VITE_APP_MAX_FILE_SIZE
            && ['image/jpeg', 'image/png', 'image/gif', 'image/heic', 'image/heif'].includes(data.type)) {
            const reader = new FileReader()

            reader.onload = e => {
                const target = e.target?.result as string

                if (!imageUrls.value.includes(target)) {
                    imageUrls.value.push(target)
                    images.push({ url: target, file: data })
                }
            }
            reader.readAsDataURL(data)
        }
    })
}

watch(() => imageUrls.value, () => {
    if (imageUrls.value.length === fileLength.value) {
        emit('imageUploaded', imageUrls.value)
    }
}, { deep: true })

const handleFile = (fileData: File) => {
    file.value = true
    emit('fileUploaded', fileData)
}

const propImage = computed(() => props.image)

watch(propImage, newValue => {
    imageUrl.value = newValue
})

const onFileChange = (event: Event) => {
    const files = (event.target as HTMLInputElement).files
    if (files) {
        if (props.mode === 'single') {
            handleSingleFile(files[0])
        } else if (props.mode === 'multiple') {
            handleMultipleFiles(files)
        } else {
            file.value = true
            handleFile(files[0])
        }
    }
}

const triggerFileInput = () => {
    fileInput.value?.click()
}

const removeFile = (index?: number) => {
    if (props.isDisabled) {
        return
    }

    imageUrl.value = null
    if (index !== undefined && index >= 0 && index <= imageUrls.value.length) {
        imageUrls.value.splice(index, 1)
    }
    emit('imageClicked', index)
}
</script>

<template>
    <div :class="(mode === 'single' || mode === 'file') ? 'file-uploader-single' : 'file-uploader' ">
        <div class="image-uploader">
            <div class="upload-section">
                <VBtn
                    :class="classBtn"
                    :disabled="isDisabled"
                    @click="triggerFileInput"
                >
                    {{ placeholder }}
                </VBtn>

                <input
                    ref="fileInput"
                    type="file"
                    :multiple="mode === 'multiple'"
                    :accept="mode === 'file' ? '' : 'image/jpeg, image/png, image/gif, image/heic, image/heif'"
                    style="display: none;"
                    @change="onFileChange"
                >
                <p
                    v-if="showInstructions"
                    class="upload-instructions"
                >
                    {{ t('messages.fileValid') }}
                </p>
            </div>
        </div>
        <div
            v-if="mode === 'single' && imageUrl && imageUrl?.length !== 0 && showFile"
            class="image-preview"
            :class="{ disabled: isDisabled }"
            @click="removeFile"
        >
            <img
                :src="imageUrl"
                alt="Uploaded Image"
            >
        </div>

        <div
            v-if="mode === 'multiple' && showFile && imageUrls.length !== 0"
            class="image-preview-multiple"
        >
            <div
                v-for="(url, index) in imageUrls"
                :key="index"
                class="image-preview-item"
                :class="{ disabled: isDisabled }"
                @click="removeFile(index)"
            >
                <img
                    :src="url"
                    alt="Uploaded Image"
                >
            </div>
        </div>

        <div
            v-if="mode === 'file' && file"
            class="file-preview"
        >
            <i class="fa fa-thin fa-file file" />
        </div>
    </div>
</template>

<style scoped>
.file-uploader {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.file-uploader-single {
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
}

.image-uploader {
    display: flex;
    align-items: center;
    flex-direction: row;
    margin-left: 20px;
}

i.file {
    font-size: 50px;
    color: #ccc;
}

.image-preview, .file-preview {
    margin-top: 10px;
    width: 100px;
    height: 100px;
    background-color: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-preview img, .image-preview-multiple img {
    max-width: 100%;
    max-height: 100%;
}

.empty-placeholder {
    width: 100%;
    height: 100%;
    background-color: #fff;
}

.upload-section {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

button {
    background-color: rgba(38, 166, 154, 1);
    padding-inline: 1rem;
    padding-block: 0.5rem;
    display: inline-block;
    border-radius: 0px;
    flex: 1;
    transition: 0.5s all;
    cursor: pointer;
    margin-bottom: 1rem;
    color: #fff;
}

button:hover {
    background-color: rgba(167, 217, 212, 1);
    color: #000;
}

.upload-instructions {
    color: #888888;
    font-size: 14px;
}

.image-preview-multiple {
    display: flex;
    flex-wrap: wrap;
    max-height: 300px;
    overflow-y: auto;
    gap: 10px;
}

.image-preview-item {
    width: 100px;
    height: 100px;
    background-color: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
