<script setup lang="ts">
import type { SelectItem } from '@/types/selectItem'
import ModalLayout from '@/dialogs/modalLayout.vue'
import { findUnitSymbolByName, listDetailedUnits, listMeasures } from '@/utils/convertUnits'
import { formatPascalCaseToLabel } from '@/helpers/textFormatter'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isAddInputModalOpen = ref(false)
const form = ref(null)
const inputCategoriesStore = useInputCategoriesStore()
const inputsStore = useInputsStore()

const formInputs = ref({
    id: '',
    label: '',
    inputType: null,
    measurementType: null,
    unitMeasurement: null,
    unitSymbol: null,
    linkCategory: null,
    sort: 0,
    stepId: '',
    updatableInput: false,
})

const inputsTypeItem = ref<SelectItem[]>([
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

const linkCategoryItems = ref<SelectItem[]>([])
const unitMeasurementsItems = ref([])
const showUnitMeasurements = ref(false)
const unitsBatchItems = ref([])
const showOptionsList = ref(false)
const optionsList = ref([])
const searchOptionsList = ref('')
const isFocused = ref<boolean>(false)

const valid = ref(false)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const typeRules = [
    (v: string) => !!v || 'Type is required',
]

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

const fetchInputCategories = async () => {
    await inputCategoriesStore.fetchInputCategories()

    linkCategoryItems.value = inputCategoriesStore.inputCategories.map((category: any) => {
        return {
            value: category.id,
            title: category.name,
        }
    })
}

$listen('openAddInputModal', async (step: any) => {
    isAddInputModalOpen.value = true
    formInputs.value = {
        id: '',
        label: '',
        inputType: null,
        measurementType: null,
        unitMeasurement: null,
        unitSymbol: null,
        linkCategory: null,
        sort: 0,
        stepId: '',
    }

    showOptionsList.value = false
    optionsList.value = []

    await fetchBatchUnits()
    await fetchInputCategories()
    formInputs.value.stepId = step.id
    formInputs.value.sort = step.inputs?.length ? step.inputs.length + 1 : 0
})

const closeAddInputModal = () => {
    isAddInputModalOpen.value = false
    showUnitMeasurements.value = false
}

const handleInputSubmitted = () => {
    $event('handleInputSubmitted')
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const inputCategories = formInputs.value.linkCategory?.map((id: string) => {
        return id !== null ? `/api/input_categories/${id}` : undefined
    }).filter(Boolean)

    const createInput = await inputsStore.createInput({
        type: formInputs.value.inputType,
        name: formInputs.value.label,
        step: `/api/steps/${formInputs.value.stepId}`,
        sort: formInputs.value.sort,
        inputCategories: formInputs.value.linkCategory ? inputCategories : [],
        unitSymbol: formInputs.value.unitSymbol || '',
        measurementType: formInputs.value.measurementType && formInputs.value.measurementType !== t('noType') ? formInputs.value.measurementType : '',
        unitMeasurement: formInputs.value.unitMeasurement || '',
        options: optionsList.value || [],
        updatable: formInputs.value.updatableInput,
    })

    if (!createInput) {
        return
    }

    handleInputSubmitted()
    isAddInputModalOpen.value = false
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
        :is-open="isAddInputModalOpen"
        name="add-input-modal"
        :title="$t('products.addInputs')"
        button-submit-text="Save"
        class="add-input"
        @modal-close="closeAddInputModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="form-wrapper">
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VTextField
                        v-model="formInputs.label"
                        :label="$t('products.inputName')"
                        variant="outlined"
                        density="compact"
                        type="text"
                        :rules="nameRules"
                    />

                    <VSelect
                        v-model="formInputs.inputType"
                        :items="inputsTypeItem"
                        variant="outlined"
                        :label="$t('products.inputType')"
                        :rules="typeRules"
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

                    <VSelect
                        v-model="formInputs.linkCategory"
                        :items="linkCategoryItems"
                        variant="outlined"
                        multiple
                        :label="$t('products.linkCategory')"
                    />

                    <VCheckbox
                        :label="$t('products.labelAutoCalculated')"
                        color="primary"
                    />

                    <VCheckbox
                        v-model="formInputs.updatableInput"
                        :label="$t('updatable')"
                        color="primary"
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
                {{ $t('products.addInputs') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.add-input.modal-mask {
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
