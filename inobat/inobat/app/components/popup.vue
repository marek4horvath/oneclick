<template>
    <v-dialog :model-value="modelValue" max-width="600" @update:model-value="updateDialog">
        <v-card class="pa-3">
            <v-card-title color="primary" class="mt-3 title">
                {{ title }}
            </v-card-title>
            <v-card-text>
                <slot name="popup-content" />
            </v-card-text>
            <v-card-actions class="pa-5">
                <v-spacer></v-spacer>
                <slot name="popup-actions" />
                <v-btn text variant="outlined" @click="closeDialog">{{ $t('close') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup lang="ts">
const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    title: {
        type: String,
        required: false,
        default: '',
    },
    content: {
        type: String,
        required: false,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const updateDialog = (value: boolean) => {
    emit('update:modelValue', value);
};

const closeDialog = () => {
    emit('update:modelValue', false);
};
</script>

<style scoped>
.title {
    background-color: rgb(var(--v-theme-primary));
}
</style>
