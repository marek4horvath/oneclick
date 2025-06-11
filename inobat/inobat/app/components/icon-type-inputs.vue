<template>
    <div class="box-icon">
        <component :is="getIcon" class="icon" />
    </div>
</template>

<script setup lang="ts">
import {
    PhImage,
    PhImages,
    PhTextAa,
    PhMapPinArea,
    PhArticleNyTimes,
    PhFileText,
    PhCalendarDots,
    PhBinary,
} from "@phosphor-icons/vue";

const iconMap = {
    text: PhTextAa,
    textarea: PhArticleNyTimes,
    image: PhImage,
    images: PhImages,
    file: PhFileText,
    numerical: PhBinary,
    coordinates: PhMapPinArea,
    dateTime: PhCalendarDots,
};

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
});

const getIcon = computed(() => iconMap[props.type] || null);
</script>

<style scoped lang="scss">
.box-icon {
    width: 42px;
    height: 42px;
    background: rgb(var(--v-theme-primary));
    ;
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
