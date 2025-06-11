<script setup lang="ts">
import type { Image } from '../types/image'
import { PhosphorIconImage } from '#components'
import FileUploader from '@/components/FileUploader.vue'

const props = defineProps({
    image: {
        type: String,
        default: '',
    },
    single: {
        type: Boolean,
        default: true,
    },
    isDisabled: {
        type: Boolean,
        default: false,
        required: false,
    },
})

const emit = defineEmits(['imageChanged'])

const newImage = ref<string | null>(null)
const isImageSizeValid = ref(true)

watch(() => props.image, newValue => {
    newImage.value = newValue
})

const onImageUploaded = (image: Image | Image[]) => {
    isImageSizeValid.value = true
    if (image) {
        if (!Array.isArray(image) && image.size > import.meta.env.VITE_APP_MAX_FILE_SIZE) {
            isImageSizeValid.value = false

            return
        }

        if (Array.isArray(image)) {
            for (const img of image) {
                if (img.size > import.meta.env.VITE_APP_MAX_FILE_SIZE) {
                    isImageSizeValid.value = false

                    return
                }
            }
        }

        newImage.value = image
        emit('imageChanged', newImage.value)
    }
}

const removeImage = (index?: number) => {
    newImage.value = props.image

    if (Array.isArray(newImage.value)) {
        newImage.value.splice(index, 1)
    } else {
        newImage.value = null
    }

    emit('imageChanged', newImage.value)
}
</script>

<template>
    <div
        class="d-flex flex-direction-row mb-5 "
        :class="{ 'align-baseline': !single }"
    >
        <div
            v-if="!newImage && image?.length === 0"
            class="rounded image-placeholder"
        >
            <PhosphorIconImage :size="32" />
        </div>

        <FileUploader
            :mode="single ? 'single' : 'multiple'"
            show-file
            show-instructions
            class-btn="uploade-btn"
            :placeholder="single ? $t('selectImage') : $t('select Images')"
            :image="image ?? undefined"
            :is-disabled="isDisabled"
            @image-clicked="removeImage"
            @image-uploaded="onImageUploaded"
        />

        <slot name="history-btn" />
    </div>
</template>

<style lang="scss">
.image-placeholder {
    width: 130px;
    height: 130px;
    background-color: #F8FAFC;
    display: flex;
    align-items: center;
    justify-content: center;

    .fa-image {
        color: #000;
        font-size: 50px;
        line-height: 120px;
        opacity: 0.05;
    }
}

.image-preview,
.image-preview-item {
    width: 130px !important;
    height: 130px !important;

    &:hover {
        position: relative;

        &::after {
            content: "";
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 130px;
            height: 130px;
            background-color: rgba(0, 0, 0, 0.4);
            background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NDggNTEyIj48IS0tIUZvbnQgQXdlc29tZSBGcmVlIDYuNS4yIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlL2ZyZWUgQ29weXJpZ2h0IDIwMjQgRm9udGljb25zLCBJbmMuLS0+PHBhdGggZD0iTTEzNS4yIDE3LjdDMTQwLjYgNi44IDE1MS43IDAgMTYzLjggMEgyODQuMmMxMi4xIDAgMjMuMiA2LjggMjguNiAxNy43TDMyMCAzMmg5NmMxNy43IDAgMzIgMTQuMyAzMiAzMnMtMTQuMyAzMi0zMiAzMkgzMkMxNC4zIDk2IDAgODEuNyAwIDY0UzE0LjMgMzIgMzIgMzJoOTZsNy4yLTE0LjN6TTMyIDEyOEg0MTZWNDQ4YzAgMzUuMy0yOC43IDY0LTY0IDY0SDk2Yy0zNS4zIDAtNjQtMjguNy02NC02NFYxMjh6bTk2IDY0Yy04LjggMC0xNiA3LjItMTYgMTZWNDMyYzAgOC44IDcuMiAxNiAxNiAxNnMxNi03LjIgMTYtMTZWMjA4YzAtOC44LTcuMi0xNi0xNi0xNnptOTYgMGMtOC44IDAtMTYgNy4yLTE2IDE2VjQzMmMwIDguOCA3LjIgMTYgMTYgMTZzMTYtNy4yIDE2LTE2VjIwOGMwLTguOC03LjItMTYtMTYtMTZ6bTk2IDBjLTguOCAwLTE2IDcuMi0xNiAxNlY0MzJjMCA4LjggNy4yIDE2IDE2IDE2czE2LTcuMiAxNi0xNlYyMDhjMC04LjgtNy4yLTE2LTE2LTE2eiIgZmlsbD0iI2Y4ZmFmYyIvPjwvc3ZnPg==");
            background-size: 24px;
            background-repeat: no-repeat;
            background-position: center;
            cursor: pointer;
        }
    }

    &.disabled:hover::after {
        display: none;
    }
}
</style>
