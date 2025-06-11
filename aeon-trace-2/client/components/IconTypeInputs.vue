<script setup lang="ts">
import {
    PhosphorIconArticleNyTimes,
    PhosphorIconBinary,
    PhosphorIconCalendarDots,
    PhosphorIconFileText,
    PhosphorIconImage,
    PhosphorIconImages,
    PhosphorIconList,
    PhosphorIconListBullets,
    PhosphorIconListChecks,
    PhosphorIconMapPinArea,
    PhosphorIconTextAa,
} from "#components"

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
})

const iconMap = {
    text: PhosphorIconTextAa,
    textarea: PhosphorIconArticleNyTimes,
    image: PhosphorIconImage,
    images: PhosphorIconImages,
    file: PhosphorIconFileText,
    numerical: PhosphorIconBinary,
    coordinates: PhosphorIconMapPinArea,
    datetime: PhosphorIconCalendarDots,
    datetimeRange: PhosphorIconCalendarDots,
    textlist: PhosphorIconList,
    checkboxlist: PhosphorIconListChecks,
    radiolist: PhosphorIconListBullets,
}

const type = props.type.toLowerCase().replace(/\s+/g, '')
const getIcon = computed(() => iconMap[type] || null)
</script>

<template>
    <div class="box-icon">
        <component
            :is="getIcon"
            class="icon"
        />
    </div>
</template>

<style scoped lang="scss">
.box-icon {
    width: 42px;
    height: 42px;
    background: #666666;
    border-radius: 5px;
    opacity: 0.3;
    position: relative;

    .icon {
        font-size: 24px;
        color: #fff;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
}
</style>
