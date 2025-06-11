<script setup lang="ts">
import { uniqBy } from 'lodash'
import type { SelectItem } from '@/types/selectItem'
import type { Steps } from '@/types/api/steps'
import type { Product } from '@/types/api/products'
import { formatPascalCaseToLabel, formatText, formatTextArray, revertFormattedText } from '@/helpers/textFormatter'
import ModalLayout from '@/dialogs/modalLayout.vue'
import { findUnitSymbolByName, listDetailedUnits, listMeasures } from '@/utils/convertUnits'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isEditStepModalOpen = ref(false)
const form = ref(null)
const processStore = useProcessStore()
const batchesStore = useBatchesStore()
const stepsStore = useStepsStore()

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
const product = ref(null)
const batchTypeOfStepItems = ref([])
const unitMeasurementsItems = ref([])
const unitsBatchItems = ref([])

const batchTypeStatus = ref<string>('')
const unitsBatchStatus = ref<string>('hidden')

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

const stepItems = computed(() => {
    return product.value?.stepsTemplate?.steps?.map((step: Steps) => {
        return {
            value: step.id,
            title: step.name,
        }
    })
})

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

$listen('openEditStepModal', async (data: { product: Product; step: Steps }) => {
    isEditStepModalOpen.value = true
    product.value = data.product

    await fetchProcess()
    await fetchBatchTypeOfStep()
    await fetchBatchUnits()

    selectedPreviousStep.value = data.step.parentSteps?.length ? data.step.parentSteps?.map((parent: any) => parent.id) : [null]
    selectedNextStep.value = data.step.steps?.length ? data.step.steps?.map((stepItem: any) => stepItem.id) : [null]

    formSteps.value = {
        id: data.step.id,
        name: data.step.name,
        process: data.step.processId,
        batch: formatText(data.step.batchTypeOfStep),
        quantity: data.step.quantity,
        previousStep: data.step.parentSteps?.map((parent: any) => parent.id),
        nextStep: data.step.steps?.map((stepItem: any) => stepItem.id),
        measurementType: data.step.measurementType,
        unitMeasurement: data.step.unitMeasurement,
        unitSymbol: data.step.unitSymbol,
    }
})

const closeEditSteptModal = () => {
    isEditStepModalOpen.value = false

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
        unitSymbol: null,
    }
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

    const editStep = await stepsStore.updateStep(formSteps.value.id, {
        name: formSteps.value.name,
        measurementType: formSteps.value.measurementType && formSteps.value.measurementType !== t('noType') ? formSteps.value.measurementType : '',
        unitMeasurement: formSteps.value.unitMeasurement || '',
        unitSymbol: formSteps.value.unitSymbol || '',
        parentSteps: formSteps.value.previousStep ? parentStepValue : [],
        steps: formSteps.value.nextStep ? childrenStep : [],
        quantity: batchTypeStatus.value !== 'hidden' ? Number.parseInt(formSteps.value.quantity) : null,
        batchTypeOfStep: revertFormattedText(formSteps.value.batch),
        process: formSteps.value.process ? `/api/processes/${formSteps.value.process}` : null,

    })

    if (!editStep) {
        return
    }

    handleStepSubmitted()
    isEditStepModalOpen.value = false
}

watch(() => formSteps.value.batch, newVal => {
    if (!newVal) {
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
            formSteps.value.unitMeasurement = null
            formSteps.value.measurementType = null
            break

        case 'DISCRETE_MULTIPLE':
            batchTypeStatus.value = 'enabled'
            unitsBatchStatus.value = 'hidden'
            formSteps.value.unitMeasurement = null
            formSteps.value.measurementType = null
            if (!formSteps.value?.quantity) {
                formSteps.value.quantity = 1
            }
            break
    }
})

watch(() => formSteps.value.measurementType, newVal => {
    const selectedUnitMeasurement = formSteps.value.unitMeasurement

    const units = listDetailedUnits(newVal)
    const unitPlura = units.map((unit: any) => unit.plural)

    if (selectedUnitMeasurement && !unitPlura.includes(selectedUnitMeasurement)) {
        formSteps.value.unitMeasurement = ''
    }

    unitMeasurementsItems.value = unitPlura
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
        :is-open="isEditStepModalOpen"
        name="edit-step-modal"
        :title="$t('products.editSteps')"
        button-submit-text="Save"
        class="edit-step"
        @modal-close="closeEditSteptModal"
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
                        variant="outlined"
                        :label="$t('products.selectProcess')"
                        :items="uniqBy(processItems, 'value')"
                        :rules="processRules"
                    />

                    <VSelect
                        v-model="formSteps.batch"
                        variant="outlined"
                        :label="$t('products.selectBatchs')"
                        :items="batchTypeOfStepItems"
                        :rules="batchRules"
                    />

                    <VSelect
                        v-model="formSteps.measurementType"
                        variant="outlined"
                        :class="{
                            hidden: batchTypeStatus !== 'hidden',
                        }"
                        :items="unitsBatchItems"
                        :label="$t('products.measurementType')"
                    />

                    <VSelect
                        v-model="formSteps.unitMeasurement"
                        variant="outlined"
                        :class="{
                            hidden: unitsBatchStatus === 'hidden',
                        }"
                        :items="unitMeasurementsItems"
                        :label="$t('products.measurementUnit')"
                    />

                    <VTextField
                        v-model="formSteps.quantity"
                        :class="{
                            hidden: formSteps.batch === 'Batch',
                        }"
                        type="number"
                        variant="outlined"
                        :label="$t('products.stepQuantity')"
                        :readonly="formSteps.batch === 'Discrete single'"
                        @wheel.prevent
                    />

                    <VSelect
                        v-model="selectedPreviousStep"
                        :items="[{ value: null, title: $t('products.noPreviousStep') }, ...uniqBy(stepItems, 'value')]"
                        variant="outlined"
                        multiple
                        :label="$t('products.previous')"
                    />

                    <VSelect
                        v-model="selectedNextStep"
                        :items="[{ value: null, title: $t('products.noNextStep') }, ...uniqBy(stepItems, 'value')]"
                        variant="outlined"
                        multiple
                        :label="$t('products.next')"
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
                {{ $t('products.editSteps') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.edit-step.modal-mask {
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
