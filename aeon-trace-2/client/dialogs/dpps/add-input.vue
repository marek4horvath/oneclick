<script setup lang="ts">
import type { SelectItem } from '@/types/selectItem'
import ModalLayout from '@/dialogs/modalLayout.vue'
import { findUnitSymbolByName, listDetailedUnits, listMeasures } from '@/utils/convertUnits'
import { formatPascalCaseToLabel } from '@/helpers/textFormatter'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isDppAddInputModalOpen = ref(false)
const form = ref(null)

const formInputs = ref({
    id: '',
    label: '',
    inputType: null,
    indexStep: 0,
    options: [],
    updatableInput: false,
    measurementType: null,
    unitMeasurement: null,
    unitSymbol: null,
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

const valid = ref(false)
const typeActionDpp = ref(false)

const showOptionsList = ref(false)
const isFocused = ref<boolean>(false)
const optionsList = ref([])
const searchOptionsList = ref('')

const unitMeasurementsItems = ref([])
const showUnitMeasurements = ref(false)
const unitsBatchItems = ref([])

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

const closeDppAddInputModal = () => {
    isDppAddInputModalOpen.value = false
    showOptionsList.value = false
    optionsList.value = []
    showUnitMeasurements.value = false
}

const handleInputSubmitted = () => {
    $event('handleDppInputSubmitted', formInputs.value)
}

const handleInputEditDppSubmitted = () => {
    $event('handleDppEditInputSubmitted', formInputs.value)
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    formInputs.value.id = Date.now()
    formInputs.value.options = optionsList.value

    if (typeActionDpp.value === 'edit-dpp') {
        handleInputEditDppSubmitted()
    } else {
        handleInputSubmitted()
    }

    isDppAddInputModalOpen.value = false
}

const openModal = (data: number) => {
    isDppAddInputModalOpen.value = true
    typeActionDpp.value = data.type
    fetchBatchUnits()

    formInputs.value = {
        id: '',
        label: '',
        inputType: null,
        indexStep: data.indexStep,
        additional: true,
        options: [],
        updatableInput: false,
        measurementType: null,
        unitMeasurement: null,
        unitSymbol: null,
    }

    optionsList.value = []
    showOptionsList.value = false
    showUnitMeasurements.value = false
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

onMounted(() => {
    $listen('openDppAddInputModal', openModal)
})
</script>

<template>
    <ModalLayout
        :is-open="isDppAddInputModalOpen"
        name="add-input-dpp-modal"
        :title="$t('products.addInputs')"
        button-submit-text="Save"
        class="add-input"
        @modal-close="closeDppAddInputModal"
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
                </VForm>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                height="45"
                @click.stop="submitHandler"
            >
                {{ $t('products.addInputs') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.add-input {
    z-index: 1005;
    .modal-container {
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
