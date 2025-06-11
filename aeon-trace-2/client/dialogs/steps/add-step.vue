<script setup lang="ts">
import { uniqBy } from 'lodash'
import type { SelectItem } from '@/types/selectItem'
import { formatPascalCaseToLabel, formatTextArray, revertFormattedText } from '@/helpers/textFormatter'
import ModalLayout from '@/dialogs/modalLayout.vue'
import { findUnitSymbolByName, listDetailedUnits, listMeasures } from '@/utils/convertUnits'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isAddStepModalOpen = ref(false)
const form = ref(null)
const processStore = useProcessStore()
const batchesStore = useBatchesStore()
const stepsStore = useStepsStore()
const stepsTemplateStore = useStepsTemplateStore()
const productsStore = useProductsStore()

const route = useRoute()
const url = route.path
const productId = url.split('/').pop()

const formSteps = ref({
    id: '',
    name: '',
    process: null,
    batch: null,
    quantity: null,
    previousStep: null,
    nextStep: null,
    measurementType: null,
    unitMeasurement: null,
    unitSymbol: null,
})

const selectedNextStep = ref([null])
const selectedPreviousStep = ref([null])

const processItems = ref<SelectItem[]>([])
const stepItems = ref<SelectItem[]>([])
const batchTypeOfStepItems = ref([])
const unitMeasurementsItems = ref([])
const unitsBatchItems = ref([])

const batchTypeStatus = ref<string>('')
const unitsBatchStatus = ref<string>('hidden')

const product = ref()

const valid = ref(false)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const processRules = [
    (v: string) => !!v || 'Process is required',
]

const batchRules = [
    (v: string) => !!v || 'Batch is required',
]

const fetchProcess = async () => {
    await processStore.fetchProcesses(undefined, undefined, 'step')

    const process = processStore.getProcessesByType('step')

    processItems.value = []

    process.forEach((processItem: any) => {
        processItems.value.push({
            value: processItem.id,
            title: processItem.name,
        })
    })

    processItems.value = processItems.value.filter(
        (item: any, index: any, self: any) =>
            index === self.findIndex((currentItem: any) => currentItem.value === item.value),
    )
}

const fetchSteps = () => {
    if (!product.value?.stepsTemplate) {
        return
    }

    product.value.stepsTemplate.steps.forEach((step: any) => {
        stepItems.value.push({
            value: step.id,
            title: step.name,
        })
    })
}

const fetchBatchTypeOfStep = async () => {
    await batchesStore.fetchBatches()

    if (!batchesStore.getBatches.batchType?.length) {
        return
    }

    batchTypeOfStepItems.value = formatTextArray(batchesStore.getBatches.batchType)
}

const fetchBatchUnits = async () => {
    const measuresType = listMeasures()

    const measuresTypeOptions = measuresType.map(measure => ({
        value: measure,
        title: formatPascalCaseToLabel(measure),
    }))

    const alphabeticallySorted = measuresTypeOptions.sort((a, b) => a.title.localeCompare(b.title))

    unitsBatchItems.value = [
        t('noType'),
        ...alphabeticallySorted,
        'batchQuantity',
    ]
}

$listen('openAddStepModal', async (data: string | any) => {
    isAddStepModalOpen.value = true

    selectedPreviousStep.value = [null]
    selectedNextStep.value = [null]

    formSteps.value = {
        id: '',
        name: '',
        process: null,
        batch: null,
        quantity: null,
        previousStep: null,
        nextStep: null,
        measurementType: null,
        unitMeasurement: null,
    }
    product.value = data
    await fetchProcess()
    await fetchBatchTypeOfStep()
    await fetchBatchUnits()
    fetchSteps()
})

const closeAddSteptModal = () => {
    isAddStepModalOpen.value = false
}

const handleStepSubmitted = () => {
    $event('handleStepSubmitted')
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const parentStepValue = formSteps.value.previousStep?.map((id: string) => {
        return id !== null ? `/api/steps/${id}` : undefined
    }).filter(Boolean)

    const childrenStep = formSteps.value.nextStep?.map((id: string) => {
        return id !== null ? `/api/steps/${id}` : undefined
    }).filter(Boolean)

    let stepTemplateId
    let sort = 0

    if (!product.value?.stepsTemplate) {
        const idProduct = product.value.id
        const nameProduct = product.value.name

        const addStepTemplateResponse = await stepsTemplateStore.createStepTemplate({
            name: `${nameProduct}StepTemplate`,
        })

        if (!addStepTemplateResponse) {
            return
        }

        const editProductResponse = await productsStore.updateProduct(idProduct,
            {
                stepsTemplate: `/api/steps_templates/${addStepTemplateResponse.id}`,
            },

        )

        if (!editProductResponse) {
            return
        }

        stepTemplateId = addStepTemplateResponse.id
    } else {
        stepTemplateId = product.value?.stepsTemplate.id
        sort = product.value?.stepsTemplate.steps.length
    }

    const createStep = await stepsStore.createStep({
        name: formSteps.value.name,
        measurementType: formSteps.value.measurementType && formSteps.value.measurementType !== t('noType') ? formSteps.value.measurementType : '',
        unitMeasurement: formSteps.value.unitMeasurement || '',
        unitSymbol: formSteps.value.unitSymbol || '',
        stepsTemplate: `/api/steps_templates/${stepTemplateId}`,
        parentSteps: formSteps.value.previousStep ? parentStepValue : [],
        steps: formSteps.value.nextStep ? childrenStep : [],
        quantity: batchTypeStatus.value !== 'hidden' ? Number.parseInt(formSteps.value.quantity) : null,
        company: null,
        batchTypeOfStep: revertFormattedText(formSteps.value.batch),
        process: formSteps.value.process ? `/api/processes/${formSteps.value.process}` : null,
        productTemplate: productId ? `api/product_templates/${productId}` : null,
        sort,
        createQr: false,
    })

    if (!createStep) {
        return
    }

    formSteps.value = {
        id: '',
        name: '',
        process: null,
        batch: null,
        quantity: null,
        previousStep: null,
        nextStep: null,
        measurementType: null,
        unitMeasurement: null,
    }

    handleStepSubmitted()
    isAddStepModalOpen.value = false
}

watch(() => formSteps.value.batch, newVal => {
    if (!newVal) {
        batchTypeStatus.value = ''
        unitsBatchStatus.value = 'hidden'

        return
    }

    switch (revertFormattedText(newVal)) {
        case 'BATCH':
            batchTypeStatus.value = 'hidden'
            formSteps.value.quantity = null
            break

        case 'DISCRETE_SINGLE':
            batchTypeStatus.value = 'disabled'
            unitsBatchStatus.value = 'hidden'
            formSteps.value.quantity = 1
            break

        case 'DISCRETE_MULTIPLE':
            batchTypeStatus.value = 'enabled'
            unitsBatchStatus.value = 'hidden'
            if (!formSteps.value?.quantity) {
                formSteps.value.quantity = 1
            }
            break
    }
})

watch(() => formSteps.value.measurementType, newVal => {
    formSteps.value.unitMeasurement = ''

    if (!newVal) {
        unitsBatchStatus.value = 'hidden'

        return
    }

    const units = listDetailedUnits(newVal)

    unitMeasurementsItems.value = units.map((unit: any) => unit.plural)
    unitMeasurementsItems.value.sort((a, b) => a.localeCompare(b))

    if (!units.length) {
        unitsBatchStatus.value = 'hidden'
    } else {
        unitsBatchStatus.value = ''
    }
})

watch(() => formSteps.value.unitMeasurement, newVal => {
    formSteps.value.unitSymbol = ''

    const unitSymbol = findUnitSymbolByName(newVal)

    formSteps.value.unitSymbol = unitSymbol
})

// Remove NULL when something else is selected and add null when nothing is selected
watch(() => selectedPreviousStep.value, (newVal: Array<string | null>, oldVal: Array<string | null>) => {
    if (newVal.includes(null) && oldVal?.includes(null) === false) {
        selectedPreviousStep.value = [null]
        formSteps.value.previousStep = [null]

        return
    }

    if (JSON.stringify(newVal) === JSON.stringify(oldVal)) {
        return
    }

    const nonNullValues = newVal.filter(id => id !== null)

    if (nonNullValues.length === 0) {
        selectedPreviousStep.value = [null]
        formSteps.value.previousStep = [null]
    } else {
        selectedPreviousStep.value = nonNullValues
        formSteps.value.previousStep = nonNullValues
    }
}, { deep: true })

watch(() => selectedNextStep.value, (newVal: Array<string | null>, oldVal: Array<string | null>) => {
    if (newVal.includes(null) && oldVal?.includes(null) === false) {
        selectedNextStep.value = [null]
        formSteps.value.nextStep = [null]

        return
    }

    if (JSON.stringify(newVal) === JSON.stringify(oldVal)) {
        return
    }

    const nonNullValues = newVal.filter(id => id !== null)

    if (nonNullValues.length === 0) {
        selectedNextStep.value = [null]
        formSteps.value.nextStep = [null]
    } else {
        selectedNextStep.value = nonNullValues
        formSteps.value.nextStep = nonNullValues
    }
})
</script>

<template>
    <ModalLayout
        :is-open="isAddStepModalOpen"
        name="add-step-modal"
        :title="$t('products.addSteps')"
        button-submit-text="Save"
        class="add-step"
        @modal-close="closeAddSteptModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="form-wrapper">
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VTextField
                        v-model="formSteps.name"
                        :label="$t('steps.name')"
                        variant="outlined"
                        density="compact"
                        type="text"
                        :rules="nameRules"
                    />

                    <VSelect
                        v-model="formSteps.process"
                        :label="$t('products.selectProcess')"
                        :items="processItems"
                        :rules="processRules"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="formSteps.batch"
                        :label="$t('products.selectBatchs')"
                        :items="batchTypeOfStepItems"
                        :rules="batchRules"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="formSteps.measurementType"
                        :class="{
                            hidden: batchTypeStatus !== 'hidden',
                        }"
                        :items="unitsBatchItems"
                        :label="$t('products.measurementType')"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="formSteps.unitMeasurement"
                        :class="{
                            hidden: unitsBatchStatus === 'hidden',
                        }"
                        :items="unitMeasurementsItems"
                        :label="$t('products.measurementUnit')"
                        variant="outlined"
                    />
                    <VTextField
                        v-model="formSteps.quantity"
                        variant="outlined"
                        :class="{
                            hidden: formSteps.batch === 'Batch',
                        }"
                        type="number"
                        :label="$t('products.stepQuantity')"
                        :readonly="formSteps.batch === 'Discrete single'"
                        @wheel.prevent
                    />

                    <VSelect
                        v-model="selectedPreviousStep"
                        :items="[{ value: null, title: $t('products.noPreviousStep') }, ...uniqBy(stepItems, 'value')]"
                        multiple
                        :label="$t('products.previous')"
                        variant="outlined"
                    />

                    <VSelect
                        v-model="selectedNextStep"
                        :items="[{ value: null, title: $t('products.noNextStep') }, ...uniqBy(stepItems, 'value')]"
                        multiple
                        :label="$t('products.next')"
                        variant="outlined"
                    />
                </VForm>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                height="45"
                @click="submitHandler"
            >
                {{ $t('products.addSteps') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.add-step.modal-mask {
    .modal-container {
        :global(.modal-body) {
            height: auto;
            padding-top: 1rem;
        }

        .modal-body {
            .form-wrapper {
                height: 400px;
                padding-top: 1rem;
                overflow-y: scroll;

            }
        }

        :global(.modal-footer) {
            margin-top: 2rem;
        }

        .modal-footer {
            .v-btn {
                padding-inline: 1rem;
                padding-block: 0.5rem;
                display: inline-block;
                border-radius: unset;
                flex: 1;
                transition: 0.5s all;

                &:hover {
                    background-color: rgba(167, 217, 212, 1) !important;
                    color: #000000 !important;
                }

                &.submit-btn {
                    background-color: rgba(38, 166, 154, 1);
                    color: #FFFFFF;
                    transition: 0.5s all;

                    &:hover {
                        background-color: rgba(167, 217, 212, 1);
                    }
                }
            }
        }
    }
}
</style>
