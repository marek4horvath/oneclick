<script setup lang="ts">
import { uniqBy } from 'lodash'
import type { SelectItem } from '@/types/selectItem'
import ModalLayout from '@/dialogs/modalLayout.vue'
import { findUnitSymbolByName, listDetailedUnits, listMeasures } from '@/utils/convertUnits'
import { formatPascalCaseToLabel } from '@/helpers/textFormatter'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()

const isEditInputModalOpen = ref(false)
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
const isFocused = ref<boolean>(false)
const optionsList = ref([])
const searchOptionsList = ref('')

const valid = ref(false)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const inputTypeRules = [
    (v: string) => !!v || 'Input type is required',
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

const fetchInputById = async (id: string) => {
    const input = await inputsStore.fetchInputById(id)

    if (!input) {
        return
    }

    return input
}

$listen('openEditInputModal', async (input: any) => {
    isEditInputModalOpen.value = true

    formInputs.value = {
        id: '',
        label: '',
        inputType: null,
        linkCategory: null,
        sort: 0,
        stepId: '',
        measurementType: null,
        unitMeasurement: null,
        unitSymbol: null,
    }

    showOptionsList.value = false
    showUnitMeasurements.value = false
    optionsList.value = []

    await fetchBatchUnits()
    await fetchInputCategories()

    const inputData = await fetchInputById(input.id)
    const inputCategoryIds = inputData.inputCategories.map((inputCategory: any) => inputCategory.id)

    formInputs.value = {
        id: input.id,
        label: input.name,
        inputType: input.type,
        linkCategory: inputCategoryIds,
        sort: input.sort,
        measurementType: input.measurementType,
        unitMeasurement: input.unitMeasurement,
        unitSymbol: input.unitSymbol,
    }

    if (input.options?.length || ['checkboxList', 'radioList', 'textList'].includes(input.type)) {
        showOptionsList.value = true
        optionsList.value = input.options
    }
})

const closeEditInputModal = () => {
    isEditInputModalOpen.value = false

    formInputs.value = {
        id: '',
        label: '',
        inputType: null,
        linkCategory: null,
        sort: 0,
        stepId: '',
        measurementType: null,
        unitMeasurement: null,
        unitSymbol: null,
    }
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

    const editInput = await inputsStore.updateInput(formInputs.value.id, {
        type: formInputs.value.inputType,
        name: formInputs.value.label,
        sort: formInputs.value.sort,
        inputCategories: formInputs.value.linkCategory ? inputCategories : [],
        unitSymbol: formInputs.value.unitSymbol || '',
        measurementType: formInputs.value.measurementType && formInputs.value.measurementType !== t('noType') ? formInputs.value.measurementType : '',
        unitMeasurement: formInputs.value.unitMeasurement || '',
        options: optionsList.value || [],
    })

    if (!editInput) {
        return
    }

    handleInputSubmitted()

    isEditInputModalOpen.value = false
}

watch(() => formInputs.value.measurementType, newVal => {
    const selectedUnitMeasurement = formInputs.value.unitMeasurement

    const units = listDetailedUnits(newVal)
    const unitPlura = units.map((unit: any) => unit.plural)

    showUnitMeasurements.value = true

    if (newVal === 'batchQuantity' || newVal === t('noType') || newVal === '') {
        showUnitMeasurements.value = false

        return
    }

    if (selectedUnitMeasurement && !unitPlura.includes(selectedUnitMeasurement)) {
        formInputs.value.unitMeasurement = ''
    }

    unitMeasurementsItems.value = unitPlura
    unitMeasurementsItems.value.sort((a, b) => a.localeCompare(b))
})

watch(() => formInputs.value.unitMeasurement, newVal => {
    formInputs.value.unitSymbol = ''

    const unitSymbol = findUnitSymbolByName(newVal)

    formInputs.value.unitSymbol = unitSymbol
})

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

const removeChip = (value: string) => {
    const index = optionsList.value.indexOf(value)
    if (index !== -1) {
        optionsList.value.splice(index, 1)
    }
}
</script>

<template>
    <ModalLayout
        :is-open="isEditInputModalOpen"
        name="edit-input-modal"
        :title="$t('products.editInputs')"
        button-submit-text="Save"
        class="edit-input"
        @modal-close="closeEditInputModal"
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
                        :items="uniqBy(inputsTypeItem, 'value')"
                        variant="outlined"
                        :label="$t('products.inputType')"
                        :rules="inputTypeRules"
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
                                @click:close="removeChip(item.value)"
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
                        :label="$t('products.labelAutoCalculated')"
                        color="primary"
                    />
                </VForm>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                @click="submitHandler"
            >
                {{ $t('products.editInputs') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.edit-input.modal-mask {
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
