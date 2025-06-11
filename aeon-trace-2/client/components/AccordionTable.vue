<script setup lang="ts">
import { PhosphorIconCaretDown, PhosphorIconCaretRight } from '#components'

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
    hiddenFooter: {
        type: Boolean,
        default: false,
    },
    hiddenTag: {
        type: Boolean,
        default: false,
    },
})

const openAccordions = ref<number[]>([])

const toggleAccordion = (id: number) => {
    if (openAccordions.value.includes(id)) {
        openAccordions.value = openAccordions.value.filter(accordionId => accordionId !== id)
    } else {
        openAccordions.value.push(id)
    }
}

const isOpen = (id: number) => openAccordions.value.includes(id)
</script>

<template>
    <div class="accordion-wrapper">
        <VCard flat>
            <VCardTitle
                v-if="title"
                class="accordion-header"
            >
                {{ props.title }}
            </VCardTitle>
            <slot name="accordion-header" />

            <VDataTable
                :headers="props.headers"
                :items="props.items"
                style="table-layout: fixed;"
                item-value="id"
                :hide-default-footer="hiddenFooter"
                :items-per-page="hiddenFooter ? -1 : 10"
            >
                <template #headers>
                    <tr class="table-header">
                        <th
                            :style="{
                                borderLeft: props.hiddenTag ? '' : `10px solid ${items[0]?.processColor}`,
                            }"
                        />
                        <th
                            v-for="header in headers"
                            :key="header.title"
                            class="text-start"
                            :class="{ 'w-33': props.fixedWidth && header.key !== 'actions', 'w-auto': fixedWidth && header.key === 'actions' }"
                        >
                            {{ header.title }}
                        </th>
                    </tr>
                </template>

                <template #body="{ items }">
                    <template
                        v-for="(item, index) in items"
                        :key="`row-${item.id}-${index}`"
                    >
                        <tr
                            class="cursor-pointer item-row"
                            @click="toggleAccordion(item.id)"
                        >
                            <td
                                v-if="item.subItems"
                                class="accordion-color-tags pa-0"
                                :style="{
                                    borderLeft: `10px solid ${item.processColor}`,
                                }"
                            >
                                <PhosphorIconCaretDown v-if="isOpen(item.id)" />
                                <PhosphorIconCaretRight v-else />
                            </td>

                            <td
                                v-else
                                class="accordion-color-tags pa-0"
                                :style="{
                                    borderLeft: `10px solid ${item.processColor}`,
                                }"
                            />
                            <td
                                v-for="header in headers"
                                :key="`item-${header.key}`"
                                :class="header.class"
                            >
                                <div v-if="header.key !== 'actions'">
                                    <template v-if="$slots['custom-coll']">
                                        <slot
                                            name="custom-coll"
                                            :item="item"
                                            :header="header"
                                        />
                                    </template>

                                    <template v-else>
                                        {{ item[header.key] || 'N/A' }}
                                    </template>
                                </div>

                                <div v-else>
                                    <div
                                        class="d-flex align-center"
                                        @click.stop
                                    >
                                        <slot
                                            name="custom-actions"
                                            :item="item"
                                        />
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr
                            v-if="item.subItems && isOpen(item.id)"
                            :key="`details-${item.id}`"
                            class="accordion-detail-wrapper"
                        >
                            <td
                                colspan="100%"
                                class="accordion-detail"
                            >
                                <slot
                                    name="accordion-content"
                                    :item="item"
                                />
                            </td>
                        </tr>
                    </template>
                </template>
            </VDataTable>

            <slot name="accordion-footer" />
        </VCard>
    </div>
</template>
