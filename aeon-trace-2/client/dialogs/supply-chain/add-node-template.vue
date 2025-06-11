<script setup lang="ts">
import { uniqBy } from 'lodash'
import ModalLayout from '@/dialogs/modalLayout.vue'
import type { SelectItem } from '~/types/selectItem'
import type { TableData } from '~/types/tableData'
import { findUnitSymbolByName, listDetailedUnits, listMeasures } from '@/utils/convertUnits'
import { formatPascalCaseToLabel } from '@/helpers/textFormatter'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()

const route = useRoute()
const url = route.path
const supplyId = url.split('/').pop()

const productTemplatesStore = useProductTemplatesStore()
const processStore = useProcessStore()
const stepsStore = useStepsStore()
const stepsTemplateStore = useStepsTemplateStore()

const isAddNodeTemplatesModalOpen = ref(false)

const name = ref('')
const label = ref('')
const process = ref(null)
const inputType = ref(null)

const processes = ref([])
const templates = ref([])
const inputs = ref([])
const unitMeasurementsItems = ref([])
const showUnitMeasurements = ref(false)
const unitsBatchItems = ref([])
const showOptionsList = ref(false)
const isFocused = ref<boolean>(false)
const optionsList = ref([])
const searchOptionsList = ref('')

const formInputs = ref({
    measurementType: null,
    unitMeasurement: null,
    unitSymbol: null,
    updatableInput: false,
})

const inputTypes = ref<SelectItem[]>([
    { title: t('inputType.text'), value: 'text' },
    { title: t('inputType.textArea'), value: 'textarea' },
    { title: t('inputType.textList'), value: 'textList' },
    { title: t('inputType.radioList'), value: 'radioList' },
    { title: t('inputType.checkboxList'), value: 'checkboxList' },
    { title: t('inputType.selectImage'), value: 'image' },
    { title: t('inputType.selectImages'), value: 'images' },
    { title: t('inputType.selectFile'), value: 'file' },
    { title: t('inputType.selectNumerical'), value: 'numerical' },
    { title: t('inputType.selectCoordinates'), value: 'coordinates' },
    { title: t('inputType.selectDateTime'), value: 'dateTime' },
    { title: t('inputType.selectDateTimeRange'), value: 'dateTimeRange' },
])

const inputsTable = ref<TableData>({
    headers: [
        { key: 'name', title: t('inputTableHeader.name') },
        { key: 'type', title: t('inputTableHeader.type') },
    ],
    idTable: 'inputTable',
    totalItems: 0,
    data: [],
})

const valid = ref(false)
const form = ref(null)

const selectedTemplate = ref(null)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.length > 0 || 'Name cannot be empty',
]

const processRules = [
    (v: string) => !!v || 'Process is required',
    (v: string) => v?.length > 0 || 'Process cannot be empty',
]

const fetchBatchUnits = () => {
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

$listen('openAddNodeTemplatesModal', async () => {
    selectedTemplate.value = null
    name.value = ''
    process.value = null
    label.value = ''
    inputType.value = null
    inputsTable.value.data = []
    await processStore.fetchProcesses()
    fetchBatchUnits()

    processes.value = uniqBy(processStore.getProcesses, 'id')

    isAddNodeTemplatesModalOpen.value = true
})

$listen('openAddNodeTemplatesModalWithEdit', async (template: any) => {
    label.value = ''
    inputType.value = null
    isAddNodeTemplatesModalOpen.value = true
    inputsTable.value.data = []
    await processStore.fetchProcesses()
    fetchBatchUnits()

    processes.value = uniqBy(processStore.getProcesses, 'id')

    selectedTemplate.value = template

    inputsTable.value.totalItems = inputsTable.value.data.length

    const inputsData = template?.stepsTemplate?.steps?.flatMap((step: any) => {
        return step.inputs?.map((input: any) => {
            if (input?.options?.length) {
                input.name += ` (${input.options.join(', ')})`
            }

            return input
        }) || []
    }) || []

    inputsTable.value.data = inputsData

    if ((inputs.value?.length < inputsData?.length) && inputs.value?.length > 0) {
        inputs.value.forEach((input: any) => {
            if (input.templateNode === template.id) {
                const exists = inputsData.some((existingInput: any) => existingInput.id === input.id)
                if (!exists) {
                    inputsData.push(input)
                }
            }
        })
        inputsTable.value.data = inputsData
    }

    if (inputsData?.length === 0) {
        inputs.value.forEach((input: any) => {
            if (input.templateNode === template.id) {
                const exists = inputsData.some((existingInput: any) => existingInput.id === input.id)
                if (!exists) {
                    inputsData.push(input)
                }
            }
        })
        inputsTable.value.data = inputsData
    }
})

const handleNodeTemplateSubmitted = (productTemplade: any) => {
    $event('handleNodeTemplateSubmitted', productTemplade)
}

const closeAddNodeTemplatesModal = () => {
    isAddNodeTemplatesModalOpen.value = false
}

const addInputHandler = async () => {
    if (!form.value) {
        return
    }

    const isValid = await form.value.validate()

    if (!isValid) {
        return
    }

    if (!label.value || !inputType.value) {
        return
    }

    const inputResponse = await useInputsStore().createInput({
        name: label.value,
        type: inputType.value,
        step: selectedTemplate.value.stepsTemplate.steps[0]['@id'],
        inputCategories: [],
        unitSymbol: formInputs.value.unitSymbol || '',
        measurementType: formInputs.value.measurementType && formInputs.value.measurementType !== t('noType') ? formInputs.value.measurementType : '',
        unitMeasurement: formInputs.value.unitMeasurement || '',
        options: optionsList.value || [],
        updatable: formInputs.value.updatableInput,
    })

    inputResponse.templateNode = selectedTemplate.value.id
    inputs.value.push(inputResponse)
    inputsTable.value.totalItems = inputs.value.length

    const inputToAdd = { ...inputResponse }

    if (inputToAdd?.options?.length) {
        inputToAdd.name += ` (${inputToAdd.options.join(', ')})`
    }

    inputsTable.value.data.push(inputToAdd)

    form.value.resetValidation()

    label.value = ''
    inputType.value = null

    optionsList.value = []
    showOptionsList.value = false

    formInputs.value.measurementType = null
    formInputs.value.unitMeasurement = null
    formInputs.value.updatableInput = false
    showUnitMeasurements.value = false
}

const submitHandler = async () => {
    if (selectedTemplate.value) {
        isAddNodeTemplatesModalOpen.value = false
        handleNodeTemplateSubmitted(selectedTemplate.value)

        return
    }

    valid.value = form.value?.validate()

    if (!valid.value) {
        return
    }

    const createProductTemplateResponse = await productTemplatesStore.createProductTemplate({
        name: name.value,
        supplyChainTemplate: `/api/supply_chain_templates/${supplyId}`,
    })

    if (!createProductTemplateResponse) {
        return
    }

    const createStepResponse = await stepsTemplateStore.createStepTemplate({
        productTemplate: `/api/product_templates/${createProductTemplateResponse.id}`,
        name: `Step Template - ${name.value}`,
        process: `/api/processes/${process.value}`,
        quantity: 1,
        sort: 0,
        batchTypeOfStep: 'DISCRETE_SINGLE',
    })

    if (!createStepResponse) {
        return
    }

    await productTemplatesStore.updateProductTemplate(createProductTemplateResponse.id, {
        stepsTemplate: `/api/steps_templates/${createStepResponse.id}`,
    })

    await stepsStore.createStep({
        name: `${name.value}`,
        process: `/api/processes/${process.value}`,
        productTemplate: `/api/product_templates/${createProductTemplateResponse.id}`,
        quantity: 1,
        sort: 0,
        stepsTemplate: `/api/steps_templates/${createStepResponse.id}`,
        batchTypeOfStep: 'DISCRETE_SINGLE',
    })

    selectedTemplate.value = await productTemplatesStore.getProductTemplate(createProductTemplateResponse.id)
}

const handleTypeInput = (type: string) => {
    if (type !== 'textList' && type !== 'radioList' && type !== 'checkboxList') {
        showOptionsList.value = false
    } else {
        showOptionsList.value = true
    }
}

const addChip = () => {
    const value = searchOptionsList.value.trim()
    if (value && !optionsList.value.includes(value)) {
        optionsList.value.push(value)
    }
    searchOptionsList.value = ''
}

watch(() => isAddNodeTemplatesModalOpen.value, async newValue => {
    if (!newValue) {
        return
    }

    templates.value = await productTemplatesStore.fetchProductTemplates()
})

watch(() => formInputs.value.measurementType, newVal => {
    formInputs.value.unitMeasurement = ''
    showUnitMeasurements.value = true

    if (newVal === 'batchQuantity' || newVal === t('noType') || !newVal) {
        showUnitMeasurements.value = false

        return
    }
    const units = listDetailedUnits(newVal)

    unitMeasurementsItems.value = units.map((unit: any) => unit.plural)
    unitMeasurementsItems.value.sort((a, b) => a.localeCompare(b))
})

watch(() => formInputs.value.unitMeasurement, newVal => {
    formInputs.value.unitSymbol = ''

    const unitSymbol = findUnitSymbolByName(newVal)

    formInputs.value.unitSymbol = unitSymbol
})
</script>

<template>
    <ModalLayout
        :is-open="isAddNodeTemplatesModalOpen"
        name="add-node-templates-modal"
        title="Node Templates"
        button-cancel-text="Cancel"
        :button-submit-text="!selectedTemplate ? 'Create' : 'Close'"
        disable-cancel-button
        style="overflow-y: scroll;"
        @modal-close="closeAddNodeTemplatesModal"
        @submit="submitHandler"
    >
        <template #content>
            <VForm
                v-if="!selectedTemplate"
                ref="form"
                class="mt-8"
            >
                <VTextField
                    v-model="name"
                    label="Name"
                    variant="outlined"
                    type="text"
                    :rules="nameRules"
                />

                <VSelect
                    v-model="process"
                    clearable
                    :label="$t('products.selectProcess')"
                    variant="outlined"
                    item-title="name"
                    item-value="id"
                    :items="processes"
                    :rules="processRules"
                />
            </VForm>

            <template v-else>
                <VForm
                    ref="form"
                    class="mt-8"
                >
                    <VTextField
                        v-model="label"
                        label="Label"
                        variant="outlined"
                        type="text"
                        :rules="nameRules"
                    />

                    <VSelect
                        v-model="inputType"
                        clearable
                        :label="$t('products.inputType')"
                        variant="outlined"
                        item-title="title"
                        item-value="value"
                        :items="uniqBy(inputTypes, 'value')"
                        :rules="processRules"
                        @update:model-value="(val) => handleTypeInput(val)"
                    />

                    <VCombobox
                        v-if="showOptionsList"
                        v-model="optionsList"
                        v-model:search="searchOptionsList"
                        :label="isFocused ? $t('products.optionsListFocus') : $t('products.optionsList')"
                        variant="outlined"
                        density="compact"
                        multiple
                        hide-no-data
                        :items="[]"
                        hide-selected
                        menu-icon=""
                        class="combobox-options-list"
                        @keydown.enter.prevent="addChip"
                        @focus="isFocused = true"
                        @blur="isFocused = false"
                    >
                        <template #selection="{ item }">
                            <VChip
                                closable
                                label
                                size="small"
                            >
                                {{ item.value }}
                            </VChip>
                        </template>
                    </VCombobox>

                    <VSelect
                        v-model="formInputs.measurementType"
                        :items="unitsBatchItems"
                        variant="outlined"
                        :label="$t('products.measurementType')"
                    />

                    <VSelect
                        v-if="showUnitMeasurements"
                        v-model="formInputs.unitMeasurement"
                        :items="unitMeasurementsItems"
                        variant="outlined"
                        :label="$t('products.measurementUnit')"
                    />

                    <VCheckbox
                        v-model="formInputs.updatableInput"
                        :label="$t('updatable')"
                        color="primary"
                    />

                    <VBtn
                        color="#26A69A"
                        variant="flat"
                        class="w-100 my-3"
                        rounded="sm"
                        style="height: 56px"
                        @click.prevent="addInputHandler"
                    >
                        Add input
                    </VBtn>
                </VForm>

                <div class="inputs-wrapper">
                    <TableData
                        search=""
                        :items="inputsTable"
                        :is-delete="false"
                        :loading="false"
                        hidden-footer
                        class="inputs-table"
                    >
                        <template #table-input-Type="{ item }">
                            <div class="d-flex align-center justify-start mx-3">
                                <IconTypeInputs
                                    class="me-2"
                                    :type="item.type"
                                />
                                <span>{{ item.type }}</span>
                            </div>
                        </template>
                    </TableData>
                </div>
            </template>
        </template>
    </ModalLayout>
</template>
