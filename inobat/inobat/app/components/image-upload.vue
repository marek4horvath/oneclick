<template>
    <v-row class="mx-1 border-thin">
        <v-col cols="12" md="3" class="d-flex align-center">
            <PhImage size="100" color="#f2f2f2" weight="thin"/>
            
            <div class="image-container" @mouseover="isHovered = true" @mouseleave="isHovered = false">
                <v-img
                    v-if="image"
                    :src="image"
                    width="150"
                    class="mr-0"
                    cover
                ></v-img>
            
                <v-overlay class="image-overlay" v-if="isHovered && image" contained v-model="image" color="#f2f2f2">
                    <PhTrash 
                        class="delete-btn" 
                        @click="deleteImage" 
                        :size="56" 
                        color="#fff"
                        weight="thin"
                    />
                </v-overlay>
            </div>
        </v-col>
        
        <v-col cols="12" md="6" class="d-flex align-center justify-center">
            <v-btn 
                color="primary" 
                @click="triggerFileInput"
                class="mb-4"
            >
                {{ $t('chooseAnImage') }}
            </v-btn>
        
            <input 
                ref="fileInput" 
                type="file" 
                accept="image/*" 
                style="display: none" 
                @change="previewImage"
            />
        </v-col>
    </v-row>
</template>

<script setup>
import { ref, defineEmits, defineProps } from 'vue';
import { PhImage, PhTrash } from "@phosphor-icons/vue";

const emit = defineEmits(['update:image']);
const props = defineProps({
    imageProp: {
        type: String,
        default: null,
    },
});

const image = ref(props.imageProp);
const fileInput = ref(null);
const isHovered = ref(false);

const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

const previewImage = (event) => {
    const file = event.target.files[0];

    if (file && file instanceof File) {
        const reader = new FileReader();
        reader.onload = (e) => {
        image.value = e.target.result;
        emit('update:image', image.value);
        };
        reader.readAsDataURL(file);
    } else {
        console.error('Invalid file or file type.');
    }
};

const deleteImage = () => {
    image.value = null;
    emit('update:image', null);
};

watch(() => props.imageProp, (newVal) => {
    image.value = newVal
});
</script>

<style scoped>
.v-img {
    margin-right: 20px;
}

.v-btn {
    margin-top: 20px;
}

.image-container {
    position: relative;
}

.delete-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    z-index: 10;
    transform: translate(-50%, -50%);
    cursor: pointer;
    display: none;
}

.image-container:hover .delete-btn {
    display: block;
}
</style>

<style>
.image-overlay .v-overlay__content {
    width: 100%;
    height: 100%;
    position: relative;
}
</style>
