<script setup lang="ts">
const props = defineProps({
    qrSrc: {
        type: String,
        required: true,
    },
})

const qrPrint = ref<string>(props.qrSrc)
</script>

<template>
    <div id="printable-content">
        <div
            class="qr"
            style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;"
        >
            <img
                :src="qrPrint"
                width="200"
            >
        </div>
    </div>
</template>
