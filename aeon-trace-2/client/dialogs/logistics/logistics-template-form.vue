<script setup lang="ts">
import { uniqBy } from 'lodash'
import { PhosphorIconPencil, PhosphorIconTrash } from '#components'
import ModalLayout from '@/dialogs/modalLayout.vue'
import InputFormModal from '~/dialogs/logistics/input-form-modal.vue'
import { useInputsStore } from '~/stores/inputs.ts'
import { useTransportationTemplateStore } from '~/stores/transportationTemplates.ts'
import type { SelectItem } from '~/types/selectItem.ts'
import type { TableData } from "~/types/tableData.ts"

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isModalOpen = ref(false)
const isEditMode = ref(false)
const inputsStore = useInputsStore()
const transportationTemplateStore = useTransportationTemplateStore()

const logisticsTemplateForm = ref({
    id: '',
    name: '',
    description: '',
    typeOfTransport: '',
    inputs: [],
})

const inputsTable = ref<TableData>({
    headers: [
        { key: 'inputName', title: t('inputTable.inputName') },
        { key: 'inputType', title: t('inputTable.inputType') },
        { key: 'actions', title: t('inputTable.actions'), sortable: false, align: 'end' },
    ],
    idTable: 'inputsTable',
    totalItems: 0,
    data: [],
})

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const typeOfTransportRules = [
    (v: string) => !!v || 'Transport type is required',
    (v: string) => v.trim().length > 0 || 'Transport type cannot be empty',
]

const closeModal = () => {
    isModalOpen.value = false
    isEditMode.value = false

    logisticsTemplateForm.value = {
        id: '',
        name: '',
        description: '',
        inputs: [],
        typeOfTransport: '',
    }
}

const transportTypesItems = ref<SelectItem[]>([])
const isLoading = ref<boolean>(true)
const inputs = ref([])

const tableOptions = ref({
    page: 1,
    itemsPerPage: 20,
    sortBy: [],
    sortDesc: [],
})

const fetchInputsTable = async (pagination?: any) => {
    isLoading.value = true

    const sortBy = pagination?.sortBy?.[0]?.key || ""
    const sortOrder = pagination?.sortBy?.[0]?.order === "desc" ? -1 : 1

    let sortedData = logisticsTemplateForm.value.inputs.map((input: any) => {
        return {
            ...input,
            inputName: input.name,
            inputType: input.type,
            id: input?.id,
        }
    })

    if (sortBy) {
        sortedData = sortedData.sort((a, b) => {
            const valA = a[sortBy] ?? ""
            const valB = b[sortBy] ?? ""

            return valA > valB ? sortOrder : valA < valB ? -sortOrder : 0
        })
    }

    inputsTable.value.data = sortedData
    inputsTable.value.totalItems = sortedData.length
    isLoading.value = false
}

const openInputModal = async (item?: any) => {
    $event('openInputModal', item)
}

const removeInput = (itemId: any) => {
    logisticsTemplateForm.value.inputs = logisticsTemplateForm.value.inputs.filter((input: any) => input.id !== itemId)
    if (!itemId.startsWith('_')) {
        inputsStore.deleteInput(itemId.split('/')[3])
    }
    fetchInputsTable()
}

onMounted(async () => {
    await inputsStore.fetchInputs()
    await transportationTemplateStore.fetchTransportTypes()
    inputs.value = inputsStore.getInputs
    transportTypesItems.value = transportationTemplateStore.transportTypes.map((type: any) => {
        return {
            value: type.id,
            title: type.name,
        }
    })
    fetchInputsTable()
})

const generateId = () => {
    return `_${Math.random().toString(36).substr(2, 9)}`
}

$listen('handleInputChanges', (newInput: any) => {
    const existingInputIndex = logisticsTemplateForm.value.inputs.findIndex(input => input.id === newInput.id)

    if (existingInputIndex !== -1) {
        logisticsTemplateForm.value.inputs[existingInputIndex] = newInput
        if (!newInput.id.startsWith('_')) {
            inputsStore.updateInput(newInput.id.split('/')[3], newInput)
        }
    } else {
        const inputWithId = {
            ...newInput,
            id: generateId(),
        }

        logisticsTemplateForm.value.inputs.push(inputWithId)
    }

    fetchInputsTable()
})

$listen('openLogisticsTemplateForm', async (item?: any) => {
    if (item?.name) {
        isEditMode.value = true
        logisticsTemplateForm.value = {
            id: item.id,
            name: item.name,
            description: item.description,
            typeOfTransport: transportationTemplateStore.transportTypes.find(
                transportType => transportType.name.toLowerCase() === item.typeOfTransport.toLowerCase(),
            ).name,
            inputs: item.inputDetails,
        }
    }

    isModalOpen.value = true
    fetchInputsTable()
})

const submitHandler = async () => {
    logisticsTemplateForm.value.inputs = logisticsTemplateForm.value.inputs.map((input, index) => {
        // eslint-disable-next-line @typescript-eslint/no-unused-vars,unused-imports/no-unused-vars
        const { id, ...rest } = input

        return {
            ...rest,
            index: index + 1,
        }
    })

    if (!logisticsTemplateForm.value.typeOfTransport?.toString()?.includes('-')) {
        logisticsTemplateForm.value.typeOfTransport = transportationTemplateStore.transportTypes.find(
            transportType => transportType.name.toLowerCase() === logisticsTemplateForm.value.typeOfTransport.toLowerCase(),
        )?.id
    }

    if (isEditMode.value) {
        // eslint-disable-next-line @typescript-eslint/no-unused-vars,unused-imports/no-unused-vars
        const { id, ...formWithoutId } = logisticsTemplateForm.value

        formWithoutId.typeOfTransport = `api/transport_types/${logisticsTemplateForm.value.typeOfTransport}`

        await transportationTemplateStore.updateTransportationTemplate(logisticsTemplateForm.value.id, formWithoutId)
    } else {
        logisticsTemplateForm.value.typeOfTransport = `api/transport_types/${logisticsTemplateForm.value.typeOfTransport}`

        // eslint-disable-next-line @typescript-eslint/no-unused-vars,unused-imports/no-unused-vars
        const { id, ...formWithoutId } = logisticsTemplateForm.value

        await transportationTemplateStore.createTransportationTemplate(formWithoutId)
    }

    isModalOpen.value = false
    isEditMode.value = false

    logisticsTemplateForm.value = {
        id: '',
        name: '',
        description: '',
        inputs: [],
        typeOfTransport: '',
    }
    $event('saveLogisticsTemplate')
}

const isFormValid = computed(() => {
    return (
        logisticsTemplateForm.value.name.trim().length > 0
            && logisticsTemplateForm.value.typeOfTransport.trim().length > 0
    )
})
</script>

<template>
    <ModalLayout
        :is-open="isModalOpen"
        name="detail-process-modal"
        :title="isEditMode ? t('logistics.editLogisticsTemplate') : t('logistics.addLogisticsTemplate')"
        button-submit-text="Save"
        class="detail-process"
        width="50vw"
        @modal-close="closeModal"
    >
        <template #content>
            <div class="process-wrapper">
                <hr style="border: 1px solid rgba(38, 166, 154, 1); margin: 0 8px 0 2px">
                <VRow style="padding-bottom: 0; padding-left: 15px">
                    <strong style="color: rgba(38, 166, 154, 1); padding: 15px 0 0 3px; font-size: 20px">Default inputs</strong>
                </VRow>
                <VRow style="padding-top: 0; padding-left: 5px">
                    <VCol cols="4">
                        <span>{{ t('logisticsTemplate.logisticsCompany') }}:</span><br>
                        <span><strong>{{ t('logisticsTemplate.companyResponsibleForTransport') }}</strong></span>
                    </VCol>
                    <VCol cols="4">
                        <span>{{ t('logisticsTemplate.typeOfTransport') }}:</span><br>
                        <span><strong>{{ t('logisticsTemplate.availableTransportTypes') }}</strong></span>
                    </VCol>
                    <VCol cols="4">
                        <span>{{ t('logisticsTemplate.departureTime') }}:</span><br>
                        <span><strong>{{ t('logisticsTemplate.datetimeFormat') }}</strong></span>
                    </VCol>
                    <VCol cols="4">
                        <span>{{ t('logisticsTemplate.arrivalTime') }}:</span><br>
                        <span><strong>{{ t('logisticsTemplate.datetimeFormat') }}</strong></span>
                    </VCol>
                    <VCol cols="4">
                        <span>{{ t('logisticsTemplate.startingPointCoordinates') }}:</span><br>
                        <span><strong>{{ t('logisticsTemplate.coordinates') }}</strong></span>
                    </VCol>
                    <VCol cols="4">
                        <span>{{ t('logisticsTemplate.destinationPointCoordinates') }}:</span><br>
                        <span><strong>{{ t('logisticsTemplate.coordinates') }}</strong></span>
                    </VCol>
                    <VCol cols="4">
                        <span>{{ t('logisticsTemplate.totalDistanceMade') }}:</span><br>
                        <span><strong>{{ t('logisticsTemplate.totalDistanceMade') }}</strong></span>
                    </VCol>
                </VRow>
                <hr style="border: 1px solid rgba(38, 166, 154, 1); margin: 0 8px 0 2px">
                <div class="form-box">
                    <VRow>
                        <VTextField
                            v-model="logisticsTemplateForm.name"
                            :label="t('logistics.formName')"
                            variant="outlined"
                            density="compact"
                            type="text"
                            :rules="nameRules"
                        />
                    </VRow>
                    <VRow>
                        <VTextarea
                            v-model="logisticsTemplateForm.description"
                            :label="t('logistics.formDescription')"
                            variant="outlined"
                        />
                    </VRow>
                    <VRow>
                        <VSelect
                            v-model="logisticsTemplateForm.typeOfTransport"
                            :label="t('logistics.formTypeTransports')"
                            :items="uniqBy(transportTypesItems, 'value')"
                            variant="outlined"
                            :rules="typeOfTransportRules"
                        />
                    </VRow>
                </div>

                <VRow>
                    <TableData
                        key="inputsTable"
                        :items="inputsTable"
                        :is-delete="false"
                        :loading="isLoading"
                        :per-page="tableOptions.itemsPerPage"
                        hidden-footer
                        @pagination="fetchInputsTable"
                    >
                        <template #table-actions="{ item }">
                            <div class="actions">
                                <PhosphorIconPencil
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    @click="openInputModal(item)"
                                />
                                <PhosphorIconTrash
                                    :size="20"
                                    color="#7d7d7d"
                                    weight="bold"
                                    class="cursor-pointer me-2"
                                    @click="removeInput(item.id)"
                                />
                            </div>
                        </template>
                    </TableData>
                </VRow>

                <VRow>
                    <VCol cols="12">
                        <VBtn
                            type="submit"
                            class="submit-btn add-input-button"
                            width="100%"
                            height="45"
                            color="#26A69A"
                            @click="openInputModal"
                        >
                            {{ t("logistics.addNewInput") }}
                        </VBtn>
                    </VCol>
                </VRow>

                <VRow>
                    <VCol cols="12">
                        <VBtn
                            class="submit-btn"
                            width="100%"
                            height="45"
                            color="#26A69A"
                            :disabled="!isFormValid"
                            @click="submitHandler"
                        >
                            {{ isEditMode ? t('logistics.editLogisticsTemplateCaps') : t('logistics.addLogisticsTemplateCaps') }}
                        </VBtn>
                    </VCol>
                </VRow>
            </div>
        </template>
        <template #footer>
            <div style="margin: 0" />
        </template>
    </ModalLayout>
    <InputFormModal />
</template>

<style lang="scss">
.process-wrapper {
    .form-box {
        margin-top: 3rem;
    }

    .custom-header {
        background-color: #26A69A !important;
    }
}
</style>
