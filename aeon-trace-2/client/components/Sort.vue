<script setup lang="ts">
interface SimpleData {
    title: string
    value: string
}

interface Emit {
    (e: 'update:Data', value: SimpleData[]): void
}

defineProps({
    requestUrl: {
        type: String,
        required: true,
    },
})

const emit = defineEmits<Emit>()

const data = ref<SimpleData[]>([])

data.value = [
    { title: 'title', value: 'value' },
    { title: 'title', value: 'value' },
]

const onButtonClick = (interval: string) => {
    // Interval should be used in request
    console.log(interval)
    emit('update:Data', data.value)
}
</script>

<template>
    <VCard class="sort-component">
        <span class="sort-by">{{ $t('sortBy') }}:</span>
        <VBtn
            variant="flat"
            class="sort-button"
            @click="onButtonClick('Day')"
        >
            Day
        </VBtn>
        <VBtn
            variant="flat"
            class="sort-button"
            @click="onButtonClick('Week')"
        >
            Week
        </VBtn>
        <VBtn
            variant="flat"
            class="sort-button"
            @click="onButtonClick('Month')"
        >
            Month
        </VBtn>
    </VCard>
</template>
