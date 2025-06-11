<script setup lang="ts">
import ContextMenu from '@imengyu/vue3-context-menu'
import { PhosphorIconHand, PhosphorIconListPlus, PhosphorIconPencilSimpleLine, PhosphorIconTrash } from "#components"
import { formatText } from '@/helpers/textFormatter'
import AddStep from '~/dialogs/steps/add-step.vue'
import UnlinkCompany from '~/dialogs/companies/unlink-company.vue'
import EditStep from '~/dialogs/steps/edit-step.vue'
import AddInputs from '~/dialogs/inputs/add-inputs.vue'
import EditInput from '~/dialogs/inputs/edit-input.vue'
import DetailStep from '~/dialogs/steps/detail-step.vue'
import DetailProcess from '~/dialogs/process/detail-process.vue'

definePageMeta({
    title: 'products.detail',
    name: 'products-detail[...id]',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { isCompanyManager } = useRoleAccess()
const { $event, $listen } = useNuxtApp()
const product = ref()
const productsStore = useProductsStore()
const processStore = useProcessStore()
const stepsStore = useStepsStore()
const inputsStore = useInputsStore()
const refreshKey = ref(0)

const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const { t } = useI18n()
const route = useRoute()
const url = route.path
const productId = url.split('/').pop()

const isLoading = ref<boolean>(true)
const processStep = ref()

const stepsHeaders = [
    { title: t('stepTableHeader.name'), key: 'name', class: 'font-weight-bold' },
    { title: t('stepTableHeader.previousStep'), key: 'previousStep' },
    { title: t('stepTableHeader.nextStep'), key: 'nextStep' },
    { title: t('stepTableHeader.quantity'), key: 'quantity' },
    { title: t('stepTableHeader.process'), key: 'process' },
    { title: t('stepTableHeader.batch'), key: 'batchTypeOfStep' },
    { title: t('stepTableHeader.numberInput'), key: 'numberInputs' },
    { title: t('stepTableHeader.actions'), key: 'actions', width: '150px' },
]

const headersInputs = [
    { title: t('inputTableHeader.name'), key: 'name' },
    { title: t('inputTableHeader.type'), key: 'type' },
    { title: t('inputTableHeader.actions'), key: 'actions', width: '150px' },
]

const steps = reactive([])

const urlProductImage = () => {
    product.value.productImage = `${backendUrl.value}/media/product_images/${product.value.productImage}`
}

const companyNamePrint = () => {
    product.value.companiesName = product.value.companies
        .map((company: any) => company.name)
        .join(', ')
}

const stepsCount = () => {
    if (!product.value.stepsTemplate) {
        product.value.stepsCount = 0

        return
    }

    product.value.stepsCount = product.value.stepsTemplate.steps?.length || 0
}

const inputsCount = () => {
    if (!product.value.stepsTemplate || !product.value.stepsTemplate.steps?.length) {
        product.value.inputsCount = 0

        return
    }

    product.value.inputsCount = product.value.stepsTemplate.steps.reduce((total: any, step: any) => {
        return total + (step.inputs?.length || 0)
    }, 0)
}

const updateSteps = async () => {
    if (!product.value?.stepsTemplate?.steps) {
        return
    }

    const stepsData = product.value.stepsTemplate.steps.map(async (step: any) => {
        const processId = step.process.split('/').pop()
        const process = await processStore.fetchProcessById(processId)

        const inputs = step.inputs?.map((input: any) => ({
            ...input,
            processColor: process?.color || '#008B8B',
        })) || []

        const nextStep = step.steps.map((step: any) => step.name).join(', ')
        const batchTypeOfStep = formatText(step.batchTypeOfStep)

        return {
            ...step,
            id: step.id ? step.id : Math.random().toString(16).substr(2, 8),
            numberInputs: step.inputs?.length || 0,
            subItems: inputs,
            process: process.name,
            processId,
            processColor: process.color,
            previousStep: step?.parentStepNames?.length ? step.parentStepNames.join(' ,') : '----',
            nextStep: nextStep || '----',
            batchTypeOfStep,
        }
    })

    steps.value = (await Promise.all(stepsData)).filter((step: any) => step !== null)
    refreshKey.value++
}

const fetchProduct = async () => {
    const responseProduct = await productsStore.fetchProductById(productId)

    await processStore.fetchProcesses(undefined, undefined, 'step')
    product.value = responseProduct
    processStep.value = processStore.getProcesses
    urlProductImage()
    companyNamePrint()
    stepsCount()
    inputsCount()
    await updateSteps()
    isLoading.value = false
}

$listen('handleStepSubmitted', async () => {
    await fetchProduct()
})

$listen('handleInputSubmitted', async () => {
    await fetchProduct()
})

$listen('handleProcessEditSubmitted', async () => {
    await fetchProduct()
})

$listen('handleProcessDeleteSubmitted', async () => {
    await fetchProduct()
})

$listen('handleProcessAddSubmitted', async () => {
    await fetchProduct()
})

$listen('productDetail', (data: any) => {
    if (data.success) {
        product.value.companies = data.product.companies
        product.value.companiesName = data.product.companies.map((company: any) => {
            return company.name
        }).join(', ')
    }
})

const openAddStepModal = () => {
    $event('openAddStepModal', product.value)
}

const openEditStepModal = (step: any) => {
    $event('openEditStepModal', { product: product.value, step })
}

const openAddInputModal = (step: any) => {
    $event('openAddInputModal', step)
}

const openEditInputModal = (input: any) => {
    $event('openEditInputModal', input)
}

const openDetailProcessModal = () => {
    $event('openDetailProcessModal')
}

const openUnlinkCompanyModal = () => {
    $event('openUnlinkCompanyModal', { productId: product.value.id, companies: product.value.companies })
}

const removeStep = async (step: any) => {
    const response = await stepsStore.deleteStep(step.id)

    if (!response) {
        return
    }

    fetchProduct()
}

const removeInput = async (input: any) => {
    const response = await inputsStore.deleteInput(input.id)

    if (!response) {
        return
    }

    fetchProduct()
}

onMounted(async () => {
    await fetchProduct()

    processStep.value = processStore.getProcessesByType('step')
})

$listen('openMenu', ({ event, nodeData }) => {
    const sourceNode = nodeData

    let step = steps.value.find((step: any) => step.id === sourceNode.id)

    if (!step) {
        step = sourceNode
    }

    const menuItems = {
        items: [
            {
                label: t('contextMenu.seeDetail'),
                onClick: () => {
                    $event('openDetailStepModal', { step })
                },
            },
            ...(isCompanyManager()
                ? [
                    {
                        label: t('contextMenu.editStep'),
                        onClick: () => {
                            $event('openEditStepModal', { product: product.value, step })
                        },
                    },
                    {
                        label: t('contextMenu.addInput'),
                        onClick: () => {
                            $event('openAddInputModal', step)
                        },
                    },
                ]
                : []),
        ],
    }

    ContextMenu.showContextMenu({
        ...menuItems,
        x: event.clientX,
        y: event.clientY,
    })
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer
            fluid
            class="detail-product"
        >
            <header>
                <VRow v-if="!isLoading">
                    <VCol
                        cols="12"
                        md="3"
                    >
                        <VImg
                            v-if="product?.productImage"
                            :src="product.productImage"
                            height="141"
                            width="212"
                            cover
                            class="ma-auto"
                            lazy-src="/assets/images/placeholder.png"
                        />
                    </VCol>

                    <VCol
                        cols="12"
                        md="9"
                    >
                        <VRow class="mt-1">
                            <h2> {{ `${t('products.name')}: ${product.name}` }}</h2>
                        </VRow>

                        <VRow class="mb-9">
                            <div class="description">
                                <p>{{ product.description }}</p>
                            </div>
                        </VRow>

                        <VRow class="footer">
                            <VCol
                                cols="12"
                                md="4"
                            >
                                {{ `${$t('companies.companies')} : ${product.companiesName}` }}
                            </VCol>

                            <VCol
                                cols="12"
                                md="4"
                            >
                                {{ `${$t('products.numberOfSteps')} : ${product.stepsCount}` }}
                            </VCol>

                            <VCol
                                cols="12"
                                md="4"
                            >
                                {{ `${$t('products.numberOfInputs')} : ${product.inputsCount}` }}
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
            </header>

            <div
                v-if="!isLoading"
                class="accordion pb-4"
            >
                <AccordionTable
                    :headers="stepsHeaders"
                    :items="steps?.value || []"
                    hidden-tag
                >
                    <template #accordion-header>
                        <div class="accordion-header">
                            <div class="w-100 d-flex align-center justify-space-between">
                                <div class="header-title" />

                                <div class="d-flex">
                                    <div class="search-wrap me-4" />

                                    <div class="actions">
                                        <VBtn
                                            v-if="isCompanyManager()"
                                            class="me-1 mt-3 mb-5 text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openAddStepModal"
                                        >
                                            {{ $t('products.addSteps') }}
                                        </VBtn>
                                        <VBtn
                                            v-if="isCompanyManager()"
                                            class="me-1 mt-3 mb-5 text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openDetailProcessModal"
                                        >
                                            {{ $t('products.addProcess') }}
                                        </VBtn>

                                        <VBtn
                                            v-if="isCompanyManager()"
                                            class="me-1 mt-3 mb-5 text-uppercase"
                                            color="#26A69A"
                                            size="large"
                                            @click="openUnlinkCompanyModal"
                                        >
                                            {{ $t('products.unlinkCompany') }}
                                        </VBtn>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template #custom-coll="{ item, header }">
                        {{ item[header.key] ?? '----' }}
                    </template>

                    <template #accordion-color-tags="{ item }">
                        <div
                            :style="{
                                height: '100%',
                                border: `1px solid ${item.processColor}`,
                                width: '0.1rem',
                            }"
                        />
                    </template>

                    <template #custom-actions="{ item }">
                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            :title="$t('actionsTablesTitle.edit')"
                            @click="openEditStepModal(item)"
                        >
                            <PhosphorIconPencilSimpleLine
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>

                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            :title="$t('actionsTablesTitle.addInput')"
                            @click="openAddInputModal(item)"
                        >
                            <PhosphorIconListPlus
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>

                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                        >
                            <PhosphorIconHand
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>

                        <VBtn
                            v-if="isCompanyManager()"
                            variant="plain"
                            class="cursor-pointer"
                            size="x-small"
                            :title="$t('actionsTablesTitle.delete')"
                            @click="removeStep(item)"
                        >
                            <PhosphorIconTrash
                                :size="20"
                                color="#7d7d7d"
                                weight="bold"
                            />
                        </VBtn>
                    </template>

                    <template #accordion-content="{ item }">
                        <AccordionTable
                            :headers="headersInputs"
                            :items="item?.subItems"
                            fixed-width
                            hidden-footer
                            class="accordion-input"
                        >
                            <template #accordion-color-tags>
                                <div
                                    style="height: 100%;
                                    border: 1px solid;
                                    width: 0.1rem;"
                                />
                            </template>
                            <template #custom-coll="{ item, header }">
                                <div v-if="header.key === 'type'">
                                    <div class="d-flex align-center">
                                        <IconTypeInputs
                                            class="me-2"
                                            :type="item[header.key]"
                                        />
                                        {{ item[header.key] ?? '----' }}
                                    </div>
                                </div>
                                <div v-else>
                                    {{ item[header.key] ?? '----' }}
                                    {{ item?.options?.length ? `(${item?.options.join(', ')})` : '' }}
                                </div>
                            </template>

                            <template #custom-actions="{ item }">
                                <VBtn
                                    v-if="isCompanyManager()"
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.updateInput')"
                                    @click="openEditInputModal(item)"
                                >
                                    <PhosphorIconPencilSimpleLine
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>

                                <VBtn
                                    v-if="isCompanyManager()"
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                >
                                    <PhosphorIconHand
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>

                                <VBtn
                                    v-if="isCompanyManager()"
                                    variant="plain"
                                    class="cursor-pointer"
                                    size="x-small"
                                    :title="$t('actionsTablesTitle.delete')"
                                    @click="removeInput(item)"
                                >
                                    <PhosphorIconTrash
                                        :size="20"
                                        color="#7d7d7d"
                                        weight="bold"
                                    />
                                </VBtn>
                            </template>
                        </AccordionTable>
                    </template>
                </AccordionTable>
            </div>
            <Legend
                v-if="processStep"
                :data="processStep"
                class="mt-8"
            />

            <VCard class="tree-container">
                <TreeFlow
                    :key="refreshKey"
                    :data="steps"
                    is-product
                    connection-key="parentSteps"
                    traversal="forward"
                    hidden-count
                />
            </VCard>
        </VContainer>

        <AddStep />
        <EditStep />
        <AddInputs />
        <EditInput />
        <DetailProcess />
        <DetailStep />
        <UnlinkCompany />
    </NuxtLayout>
</template>

<style lang="scss">
.tree-container {
    width: 95%;
    margin: 2rem auto;
}

.box-legend {
    width: 95% !important;
}
</style>
