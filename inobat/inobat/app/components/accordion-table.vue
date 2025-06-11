<template>
    <div class="accordion-wrapper">
        <v-card flat>
            <v-card-title v-if="title" class="accordion-header">{{ title }}</v-card-title>

            <v-data-table :headers="headers" :items="items" style="table-layout: fixed;" hide-default-footer item-value="id">
                <template v-slot:headers>
                    <tr class="table-header">
                        <th></th>
                        <th 
                            v-for="header in headers"
                            :key="header.title"
                            class="text-start"
                            :class="{ 'w-33': fixedWidth && header.key !== 'actions', 'w-auto': fixedWidth && header.key === 'actions' }"
                        >
                            {{ header.title }}
                        </th>
                    </tr>
                </template>

                <template v-slot:body="{ items }">
                    <template v-for="(item, index) in items" :key="`row-${item.id}-${index}`">
                        <tr class="cursor-pointer item-row" @click="toggleAccordion(item.id)">
                            <td>
                                <v-icon v-if="item.subItems">
                                    {{ isOpen(item.id) ? 'mdi-chevron-down' : 'mdi-chevron-right' }}
                                </v-icon>
                            </td>
                            <td v-for="header in headers" :key="`item-${header.key}`">
                                <div v-if="header.key !== 'actions'">
                                    <template v-if="$slots['custom-coll']">
                                        <slot name="custom-coll" :item="item" :header="header" />
                                    </template>

                                    <template v-else>
                                        {{ item[header.key] || 'N/A' }}
                                    </template>
                                </div>

                                <div v-else>
                                    <div @click.stop class="d-flex align-center">
                                        <slot name="custom-actions" :item="item" />
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="item.subItems && isOpen(item.id)" :key="`details-${item.id}`"
                            class="accordion-detail-wrapper">
                            <td colspan="100%" class="accordion-detail">
                                <slot name="accordion-content" :item="item" />
                            </td>
                        </tr>
                    </template>
                </template>
            </v-data-table>

            <slot name="accordion-footer" />
        </v-card>
    </div>
</template>

<script setup lang="ts">
const props = defineProps({
    title: {
        type: String,
        default: null,
    },
    headers: {
        type: Array as () => { title: string; key: string }[],
        required: true,
    },
    fixedWidth: {
        type: Boolean,
        default: false,
    },
    items: {
        type: Array as () => any[],
        required: true,
    },
});

const openAccordions = ref<number[]>([]);

const toggleAccordion = (id: number) => {
    if (openAccordions.value.includes(id)) {
        openAccordions.value = openAccordions.value.filter((accordionId) => accordionId !== id);
    } else {
        openAccordions.value.push(id);
    }
};

const isOpen = (id: number) => openAccordions.value.includes(id);

</script>
