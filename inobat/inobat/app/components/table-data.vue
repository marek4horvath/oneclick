<template>
    <div class="table-wrapper">
        <v-card flat>
            <v-card-title class="d-flex align-center px-0 mx-0">
                <slot name="table-title" />
            </v-card-title>

            <v-divider></v-divider>

            <v-data-table 
                :search="search" 
                :filter-keys="['name']" 
                :headers="items.headers"
                :items="items.data"
                @click:row="handleRowClick"
                :items-per-page-options="[5, 10, 20, 100]"
                @update:options="handleUpdateOptions"
            >
                <!-- Slot pre akcie v tabuľke -->
                <template v-slot:item.actions="{ item }">
                    <div @click.stop>
                        <slot name="table-actions" :item="item" />
                    </div>
                </template>

                <!-- Slot pre obrázky -->
                <template v-slot:item.image="{ item }">
                    <div v-if="item && item.image">
                        <slot name="table-image" :item="item" />
                    </div>
                </template>
            </v-data-table>
        </v-card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

const props = defineProps({
    search: {
        type: String,
        required: true,
    },
    items: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['set:rowData', 'fetch:items']);

// Emitovanie dát po kliknutí na riadok
const handleRowClick = (event: PointerEvent, rowData: any) => {
    emit('set:rowData', rowData.item);
};

// Funkcia na načítanie údajov po zmene možností tabuľky
const handleUpdateOptions = (options: any) => {
    emit('fetch:items', options);
    console.log(options);
};

</script>
