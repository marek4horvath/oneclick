<template>
    <div id="printable-content">
        <div
            class="qr"
        >
            <img
                :src="qrPrint"
                width="400"
            >
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps({
    qrSrc: {
        type: String,
        required: true,
    },
});

const qrPrint = ref<string>('');

watch(() => props.qrSrc, (newData) => {
    qrPrint.value = newData;
}, { immediate: true });
</script>

<style>

.qr {
    display: flex;
    justify-content: center;
    align-items: center; 
    width: 100%; 
    height: 100%;
}

</style>
