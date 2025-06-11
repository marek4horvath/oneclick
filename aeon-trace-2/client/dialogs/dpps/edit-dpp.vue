<script lang="ts" setup>
import mimedb from 'mime-db'
import ModalLayout from '@/dialogs/modalLayout.vue'
import InputByType from '@/components/InputByType.vue'
import TreeFlow from '~/components/tree/TreeFlow.vue'
import { b64toBlob } from '@/helpers/convert'
import type { MessageBag } from '@/types/index'
import type { SelectItem } from '@/types/selectItem'
import type { Step } from "~/types/api/productTemplate.ts"
import { formatPascalCaseToLabel } from '@/helpers/textFormatter'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isEditDppModalOpen = ref(false)

const logisticsStore = useLogisticsStore()
const dppStore = useDppStore()
const nodeStore = useNodesStore()
const processStore = useProcessStore()
const companiesStore = useCompaniesStore()
const productsStore = useProductsStore()
const router = useRouter()
const nodeData = ref()
const nodeDataSteps = ref()
const nodeParentsData = ref()
const dynamicallyAddedInputs = ref<Record<number, string[]>>({})
const confirmedSteps = ref<Record<number, boolean>>({})

const isLoading = ref({
    company: true,
    site: true,
})

const inputs = ref([])
const productInputs = ref([])
const lockedInputs = ref<Record<string, boolean>>({})
const existingAdditionalInputs = ref<Record<string, string[]>>({})

const form = ref(null)
const valid = ref(false)
const companies = ref<SelectItem[]>([])
const sites = ref([])
const logisticsItem = ref([])
const prouctInputIds = ref({})
const productInputFile = ref({})
const productInputDate = ref({})
const isUpdatable = ref({})
const logisticIds = ref<any[]>([])
const stepsState = ref([])
const fetchedSteps = ref()

const formValues = ref({
    idQr: '',
    company: null,
    site: null,
    logistic: null,
    inputs: [],
    measurementValue: [],
    measurementValueProductSteps: [],
    logistics: {},
    dpp: {},
    productStepsDpp: {},
    updatableInput: [],
})

const dppCreationStatus = ref({
    createDpp: false,
    ongoingDpp: false,
    emptyDpp: true,
    btnLable: t('dpps.createEmptyDpp'),
})

const MEASUREMENT_TYPE = 'MEASUREMENT_TYPE'

const activeSteps = ref([])
const activeStepsData = ref([])
const inputData = ref([])
const isConfirmFilled = ref(false)
const isSubmited = ref(false)
const previousInputData = ref<Record<string, any>>({})
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)
const dpp = ref()
const dppImge = ref({})
const dppImges = ref({})
const typeDpp = ref()
const stateDpp = ref()
const dppsByNode = ref()
const selectedDpps = ref<Record<string, any[]>>({})
const selectedLogistics = ref<Record<string, any[]>>({})
const selectedFromProductSteps = ref<Record<string, any[]>>({})
const dpps = ref<Record<string, any[]>>({})
const productStepsDpp = ref<Record<string, any[]>>({})
const showProductStepsInput = ref<Record<string, boolean>>({})
const prevProductStepSelections: Record<string, string[]> = {}
const seenIds = new Set()
const productStepsId = new Set()
const fromDpps = ref()

const companyRules = [
    (v: string) => !!v || 'Company is required',
]

const closeEditDppModal = () => {
    isEditDppModalOpen.value = false
    logisticsItem.value = []
    formValues.value = {
        idQr: '',
        company: null,
        site: null,
        logistic: null,
        inputs: [],
        logistics: {},
        dpp: {},
        productStepsDpp: {},
        measurementValue: [],
        measurementValueProductSteps: [],
        updatableInput: [],
    }
    seenIds.clear()
    productStepsId.clear()
    stepsState.value = []

    Object.keys(prevProductStepSelections).forEach(key => {
        delete prevProductStepSelections[key]
    })

    if (nodeData.value?.steps) {
        nodeData.value.steps.forEach(step => {
            showProductStepsInput.value[step.id] = false
            step.inputs = step.inputs.filter(input => !input.additional)
        })
    }

    nodeData.value = null
    nodeDataSteps.value = null
    dynamicallyAddedInputs.value = {}
    confirmedSteps.value = {}
    activeSteps.value = []
    activeStepsData.value = []
    inputData.value = []
    inputs.value = []
    isConfirmFilled.value = false
    prouctInputIds.value = {}
    logisticIds.value = []
    dppImge.value = {}
    dppImges.value = {}
    selectedDpps.value = {}
    selectedLogistics.value = {}
    selectedFromProductSteps.value = {}
    dpps.value = {}
    productStepsDpp.value = {}
    showProductStepsInput.value = {}
    fromDpps.value = null
}

const fetchCompanies = async () => {
    let companyData = []

    if (nodeData.value.productTemplates && nodeData.value.productTemplates.length > 0) {
        companyData = await Promise.all(
            nodeData.value.productTemplates.map(async (productTemplateId: any) => {
                const p = await useProductsStore().fetchCompanyProducts(productTemplateId.split('/').pop())

                return p.companies.map((company: any) => ({
                    value: company.id,
                    title: company.name,
                    sites: company.sites,
                }))
            }),
        )
    } else if (nodeData.value.nodeTemplate) {
        // The situation needs to be discussed in case the node has only a node template assigned, but the company should still be displayed.
        // Currently, the node template does not own any companies.
        const companiesRequest = await companiesStore.fetchCompaniesListing(undefined, undefined, undefined, false, true, undefined, undefined)

        companyData = companiesRequest.map((company: any) => ({
            value: company.id,
            title: company.name,
            sites: company.sites,
        }))
    }

    companies.value = companyData.flat()

    isLoading.value.company = false
    isLoading.value.site = false
}

const userId = ref(null)

const validateSteps = (steps: any, inputsData: any, requireAllInputs = true) => {
    const validationResult = steps
        .filter((step: any) => step.inputs && step.inputs.length > 0)
        .map((step: any) => {
            const stepInputs = step?.inputs.map((input: any) => inputsData[input.id]).filter(Boolean)

            const measurement = formValues.value.measurementValueProductSteps?.[step.id]

            const hasMeasurement
                = step.batchTypeOfStep === 'BATCH'
                && step.measurementType
                && !!measurement

            const filledInputs = stepInputs.filter((input: any) => {
                // If the type is "MEASUREMENT TYPE", check the measurement Value Inputs.
                if (input.inputValue === MEASUREMENT_TYPE) {
                    return input.measurementValueInputs !== undefined
                        && input.measurementValueInputs !== null
                        && input.measurementValueInputs !== ''
                }

                return input.inputValue !== undefined
                        && input.inputValue !== null
                        && input.inputValue !== ''
                        && (!Array.isArray(input.inputValue) || input.inputValue.length > 0)
            })

            const inputsToCheck = step.inputs.filter((input: any) => {
                return !(input.type === 'textList' && (!input?.measurementType || input?.measurementType === ''))
            })

            const hasAllInputs = requireAllInputs
                ? filledInputs.length === inputsToCheck.length
                : filledInputs.length > 0

            const shouldValidateExtraFields = nodeData.value?.parents?.length > 0

            const logistics = shouldValidateExtraFields ? formValues?.value?.logistics?.[step.id] : true
            const dppData = shouldValidateExtraFields ? formValues?.value?.dpp?.[step.id] : true

            const hasLogistics = shouldValidateExtraFields ? !!logistics : true
            const hasDpp = shouldValidateExtraFields ? !!dppData : true

            let hasProductStepsDpp = true

            if (showProductStepsInput.value[step.id] === true) {
                const productStepsDppData = shouldValidateExtraFields ? formValues?.value?.productStepsDpp?.[step.id] : true

                hasProductStepsDpp = shouldValidateExtraFields
                    ? Array.isArray(productStepsDppData) && productStepsDppData.length > 0
                    : true
            }

            let isValid = hasAllInputs && hasLogistics && hasDpp && hasProductStepsDpp

            if (isValid && !hasMeasurement && step.batchTypeOfStep === 'BATCH') {
                isValid = false
            }

            const noInputsOrMeasurements = filledInputs.length === 0 && !hasMeasurement

            const isProductStepsEmpty = showProductStepsInput.value[step.id]
                ? !Array.isArray(formValues?.value?.productStepsDpp?.[step.id]) || formValues.value.productStepsDpp[step.id].length === 0
                : true

            const isEmptyAllInputs
                = noInputsOrMeasurements
                && (!shouldValidateExtraFields || (
                    (!logistics || logistics === null)
                    && (!dppData || dppData === null)
                    && isProductStepsEmpty
                ))

            return {
                stepId: step.id,
                stepName: step.name,
                isValid,
                isEmptyAllInputs,
            }
        })

    const allStepsValid = validationResult.every((result: any) => result.isValid)

    return {
        allStepsValid,
        validationResult,
    }
}

const initializeForm = (step: any, newInputs: boolean = false) => {
    nodeDataSteps.value = nodeDataSteps.value ? nodeDataSteps.value : nodeData.value

    if (newInputs) {
        fetchedSteps.value?.steps.forEach((fetchedStep: any) => {
            const matchingNodeStep = nodeDataSteps.value.steps.find(
                (nodeStep: any) => nodeStep.id === fetchedStep.id,
            )

            if (matchingNodeStep) {
                matchingNodeStep.inputs = fetchedStep.inputs
            }
        })
    }

    inputs.value = Array.from(
        new Set([
            ...inputs.value,
            ...nodeDataSteps.value.steps.flatMap((nodeStep: any) => nodeStep.inputs),
        ]),
    )

    const stepTemplateReference = typeDpp.value === 'ProductStep' ? step.stepTemplateReference.id : step.stepTemplateReference.split('/').pop()

    nodeDataSteps.value.steps.forEach((nodeStep: any) => {
        if (nodeStep.id === stepTemplateReference) {
            nodeStep.inputs?.forEach((nodeStepInput: any) => {
                const matchingProductInput = step.productInputs.find(
                    (productInput: any) => productInput.name === nodeStepInput.name,
                )

                if (!matchingProductInput) {
                    return
                }

                const type = matchingProductInput.type.toLowerCase().replace(/\s+/g, '')

                switch (type) {
                    case 'image':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.image
                            ? `${backendUrl.value}/media/product_input_images/${matchingProductInput.image}`
                            : undefined
                        dppImge.value[nodeStepInput.id] = matchingProductInput.image || null
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'images':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.images.map(
                            (img: any) => `${backendUrl.value}/media/product_input_collection_images/${img.image}`,
                        )
                        dppImges.value[nodeStepInput.id] = matchingProductInput.images.map(
                            (img: any) => `${backendUrl.value}/media/product_input_collection_images/${img.image}`,
                        ) || null
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'file':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.document
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        productInputFile.value[nodeStepInput.id] = matchingProductInput.document
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'text':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.textValue
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'textarea':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.textAreaValue
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'numerical':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.numericalValue
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'datetime':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.dateTimeTo
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        productInputDate.value[nodeStepInput.id] = matchingProductInput.dateTimeTo
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'datetimerange':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.dateTimeTo
                            ? [matchingProductInput.dateTimeFrom, matchingProductInput.dateTimeTo]
                            : [matchingProductInput.dateTimeFrom]
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'coordinates':
                        formValues.value.inputs[nodeStepInput.id] = {
                            lat: matchingProductInput.latitudeValue,
                            lng: matchingProductInput.longitudeValue,
                        }
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'checkboxlist':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.checkboxValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'radiolist':
                        formValues.value.inputs[nodeStepInput.id] = matchingProductInput.radioValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        if (matchingProductInput?.id) {
                            prouctInputIds.value[nodeStepInput.id] = matchingProductInput.id
                        }
                        break

                    case 'textlist':
                        formValues.value.measurementValue[nodeStepInput.id] = matchingProductInput.measurementValue
                        isUpdatable.value[nodeStepInput.id] = matchingProductInput.updatable
                        formValues.value.updatableInput[nodeStepInput.id] = isUpdatable.value[nodeStepInput.id]
                        break
                }

                if (matchingProductInput.locked) {
                    lockedInputs.value[nodeStepInput.id] = true
                }
            })
        }
    })
}

const fetchLogistics = async (id: string) => {
    const getLogisticsResponse = await logisticsStore.fetchLogisticsById(id)

    nodeData.value.logisticsItem = []
    if (!getLogisticsResponse) {
        return
    }

    if (!getLogisticsResponse?.logisticsParent) {
        return getLogisticsResponse
    }

    if (
        nodeData.value['@id'] === getLogisticsResponse.toNode
        && !logisticIds.value.includes(getLogisticsResponse.logisticsParent.id)
    ) {
        logisticIds.value.push(getLogisticsResponse.logisticsParent.id)

        return getLogisticsResponse
    }
}

const autoFetchDataLogistic = async () => {
    try {
        if (!dpp.value?.materialsReceivedFrom?.length) {
            return
        }

        const receivedLogistics = await Promise.all(
            dpp.value.materialsReceivedFrom.map(async (logisticsRef: string) => {
                const logisticsId = logisticsRef.split('/').pop()
                if (!logisticsId) {
                    return null
                }

                return await fetchLogistics(logisticsId)
            }),
        )

        const validLogistics = receivedLogistics.filter(Boolean)
        if (!validLogistics.length) {
            return
        }

        const firstLogistic = validLogistics[0]
        const fromDppUrls = firstLogistic.fromDpps || []
        const stepId = dpp.value.stepTemplateReference.id

        if (!fromDppUrls.length) {
            formValues.value.dpp[stepId] = firstLogistic.fromProductSteps[0]?.split('/').pop()
            formValues.value.logistics[stepId] = firstLogistic.id

            return
        }

        const fromDppIds = fromDppUrls.map((dppUrl: string) => dppUrl.split('/').pop()).filter(Boolean)

        const fetchedDpps = await useDppStore().fetchDppByIds(fromDppIds)
        const selectedDpp = fetchedDpps?.dpps?.[0]

        if (!selectedDpp) {
            return
        }

        formValues.value.dpp[stepId] = dpps.value?.length ? selectedDpp.id : null
        formValues.value.logistics[stepId] = firstLogistic.id

        const productSteps = selectedDpp.productSteps || []

        showProductStepsInput.value[stepId] = productSteps.length > 0

        productStepsDpp.value[stepId] = productSteps.map((ps: any) => ({
            title: ps.name,
            value: ps.id,
        }))
    } catch (error) {
        console.error("The error occurred during autoFetchDataLogistic execution:", error)
    }
}

const getStateByStep = (stepId: string) => {
    let infoState: any = {
        title: t('dpps.state.emptyDpp'),
        class: 'empty',
    }

    if (dpp?.value?.state) {
        switch (dpp?.value?.state) {
            case 'ONGOING_DPP':
                infoState = {
                    title: t('dpps.state.ongoingDpp'),
                    class: 'ongoing',
                    ongoingDpp: true,
                    createEmptyDpp: false,
                }
                break

            case 'EMPTY_DPP':
                infoState = {
                    title: t('dpps.state.emptyDpp'),
                    class: 'empty',
                    ongoingDpp: false,
                    createEmptyDpp: true,
                }
                break
        }
    }

    if (!stepId && !stepsState.value) {
        return infoState
    }

    const stepState = stepsState.value.find((stepStateItem: any) => stepStateItem.stepId === stepId)

    if (!stepState) {
        return infoState
    }

    if (!stepState.isEmptyAllInputs && !stepState.isValid) {
        infoState = {
            title: t('dpps.state.ongoingDpp'),
            class: 'ongoing',
            ongoingDpp: true,
            createEmptyDpp: false,
        }
    }

    if (stepState.isEmptyAllInputs && !stepState.isValid) {
        infoState = {
            title: t('dpps.state.emptyDpp'),
            class: 'empty',
            ongoingDpp: false,
            createEmptyDpp: true,
        }
    }

    if (!stepState.isEmptyAllInputs && stepState.isValid) {
        infoState = {
            title: t('dpps.state.finishDpp'),
            class: 'finish',
            ongoingDpp: false,
            createEmptyDpp: false,
        }
    }

    return infoState
}

const isFetching = ref(false)

const handleOpenEditDppModal = async (data: any) => {
    if (isFetching.value) {
        return
    }

    isFetching.value = true
    try {
        isEditDppModalOpen.value = true
        formValues.value.inputs = []
        inputs.value = []
        dpps.value = []
        productStepsDpp.value = []
        typeDpp.value = data.type
        stateDpp.value = data.state

        nodeData.value = null
        nodeDataSteps.value = null
        if (data.type === 'ProductStep') {
            dpp.value = await productsStore.fetchProductStepById(data.id as string)
        } else {
            dpp.value = await dppStore.fetchDpp(data.id)
        }

        if (typeof data.node === 'string') {
            const nodeId = data.node.split('/').pop()

            nodeData.value = await nodeStore.fetchNode(nodeId)
            fetchedSteps.value = await nodeStore.fetchNodeSteps(nodeId)

            if (Array.isArray(fetchedSteps.value) && fetchedSteps.value?.steps.length) {
                nodeData.value.steps = fetchedSteps.value?.steps
            }

            if (nodeData.value.nodeTemplate) {
                const nodeTemplateId = nodeData.value.nodeTemplate.split('/').pop()
                if (nodeTemplateId) {
                    const nodeDataResponse = await useProductsStore().fetchProductById(nodeTemplateId)

                    if (nodeDataResponse && nodeDataResponse.stepsTemplate.steps) {
                        const newSteps = nodeDataResponse.stepsTemplate.steps.map((newStep: any) => ({
                            ...newStep,
                            parentStepNames: newStep.parentStepNames || [],
                            steps: newStep.steps || [],
                            process: newStep.process || "",
                            quantity: newStep.quantity || 0,
                            sort: newStep.sort || 0,
                            stepImage: newStep.stepImage || "",
                            unitMeasurement: newStep.unitMeasurement || "",
                        }))

                        const uniqueNewSteps = newSteps.filter((newStep: any) => {
                            return !nodeData.value.steps.some((existingStep: any) => existingStep.id === newStep.id)
                        })

                        nodeData.value.steps = [...nodeData.value.steps, ...uniqueNewSteps]
                    }
                }
            }
        } else {
            nodeData.value = data.node

            if (nodeData.value.nodeTemplate) {
                const nodeTemplateId = nodeData.value.nodeTemplate.split('/').pop()
                if (nodeTemplateId) {
                    const nodeDataResponse = await useProductsStore().fetchProductById(nodeTemplateId)

                    if (nodeDataResponse && nodeDataResponse.stepsTemplate.steps) {
                        const newSteps = nodeDataResponse.stepsTemplate.steps.map((newStep: any) => {
                            return {
                                ...newStep,
                                parentStepNames: newStep.parentStepNames || [],
                                steps: newStep.steps || [],
                                process: newStep.process || "",
                                quantity: newStep.quantity || 1,
                                sort: newStep.sort || 0,
                                stepImage: newStep.stepImage || "",
                                unitMeasurement: newStep.unitMeasurement || "",
                            }
                        })

                        const uniqueNewSteps = newSteps.filter((newStep: any) => {
                            return !nodeData.value.steps.some((existingStep: any) => existingStep.id === newStep.id)
                        })

                        nodeData.value.steps = [...nodeData.value.steps, ...uniqueNewSteps]
                    }
                }
            }
        }

        if (dpp.value.productInputs && dpp.value.productInputs.length > 0) {
            dpp.value.productInputs.forEach((productInput: any) => {
                if (nodeData.value.steps && nodeData.value.steps.length > 0) {
                    nodeData.value.steps.forEach((step: any) => {
                        const inputExists = step.inputs.some((input: any) =>
                            input.id === productInput.id || input.name === productInput.name,
                        )

                        if (!inputExists) {
                            step.inputs.push({
                                id: productInput.id,
                                type: productInput.type,
                                name: productInput.name,
                                additional: productInput.additional,
                                categories: productInput.inputCategories || [],
                            })

                            formValues.value.inputs[productInput.id] = productInput.textValue
                                                                       || productInput.numericalValue
                                                                       || productInput.dateTimeValue
                                                                       || null
                        }
                    })
                }
            })
        }

        const nodeDataLocal = { ...nodeData.value }

        await fetchCompanies()
        await autoFetchDataLogistic()
        formValues.value.company = dpp.value?.company.id
        formValues.value.site = dpp.value?.companySite?.id

        if (dpp.value.stepTemplateReference) {
            formValues.value.measurementValueProductSteps[dpp.value.stepTemplateReference.id] = dpp.value?.measurementValue || null
        }

        if (data.type === 'ProductStep') {
            nodeDataLocal.steps
                .forEach((nodeDataLocalStep: any) => {
                    fetchedSteps.value?.steps.forEach((step: any) => {
                        if (nodeDataLocalStep.id === step.id) {
                            nodeDataLocalStep.inputs = step.inputs
                        }
                    })
                })

            nodeDataSteps.value = nodeDataLocal
            nodeDataSteps.value.steps = []

            nodeDataSteps.value.steps = nodeData.value.steps?.length
                ? nodeData.value.steps.filter((step: any) => step.id === dpp.value.stepTemplateReference.id)
                : nodeData.value.subItems.filter((step: any) => step.id === dpp.value.stepTemplateReference.id)

            initializeForm(dpp.value)
        } else {
            if (!dpp.value?.productSteps?.length) {
                return
            }

            for (const productStep of dpp.value.productSteps) {
                let productStepId: string | null = null

                if (productStep.state === "NOT_ASSIGNED") {
                    productStepId = productStep.stepTemplateReference.split('/').pop()
                    formValues.value.measurementValueProductSteps[productStepId] = productStep?.measurementValue || null
                }

                productInputs.value[productStep.id] = {
                    productStep,
                    inputs: productStep.productInputs,
                }

                if (nodeDataSteps.value?.steps) {
                    nodeDataSteps.value.steps = nodeDataLocal.steps
                }

                initializeForm(productStep, true)
            }
        }

        if (nodeData.value?.countLogisticsNext) {
            logisticIds.value = []
            nodeData.value.logisticsItem = []

            const assignedIds = nodeData.value.countLogisticsNext.assignedToDppData || []
            const exportIds = nodeData.value.countLogisticsNext.exportLogisticsData || []

            const allLogisticsIds = [...assignedIds, ...exportIds]

            const fetchedLogistics = await Promise.all(
                allLogisticsIds.map(async (logisticsRef: string) => {
                    const id = logisticsRef.split('/').pop()

                    return await fetchLogistics(id)
                }),
            )

            const items = []
            const idLogistics = []

            for (const logisticItem of fetchedLogistics) {
                const parentId = logisticItem.logisticsParent.id

                if (seenIds.has(parentId)) {
                    continue
                }

                seenIds.add(parentId)

                if (logisticItem.logisticsParent.fromDpps?.length) {
                    idLogistics.push(logisticItem.logisticsParent.id)
                }

                if (logisticItem.logisticsParent.fromProductSteps?.length) {
                    selectedFromProductSteps.value[parentId] = {
                        idLogistics: parentId,
                        fromProductSteps: logisticItem.logisticsParent.fromProductSteps,

                    }

                    items.push({
                        title: logisticItem.logisticsParent.name,
                        value: parentId,
                    })
                }
            }

            const filterLogistics = await logisticsStore.fetchLogisticsfilterOutExported(idLogistics)

            fromDpps.value = filterLogistics

            nodeData.value.logisticsItem = filterLogistics.logistics.map((logistic: any) => {
                return {
                    title: logistic.name,
                    value: logistic.id,
                }
            })

            if (items?.length) {
                nodeData.value.logisticsItem.push(...items.filter(Boolean))
            }
        }

        if (nodeData.value?.steps) {
            nodeData.value.steps.forEach((step, index) => {
                existingAdditionalInputs.value[index] = step.inputs
                    .filter(input => input.additional)
                    .map(input => input.id)
            })
        }

        const authStore = useAuthStore()

        userId.value = await authStore.getMyId()
    } catch (error) {
        console.error(error)
    } finally {
        isFetching.value = false
    }
}

onMounted(() => {
    $listen('openEditDppModal', handleOpenEditDppModal)

    onUnmounted(() => {
        if ($event && typeof $event.off === 'function') {
            $event.off('openEditDppModal', handleOpenEditDppModal)
        }
    })
})

const stepsNode = computed(() => {
    const localSteps: Step[] = []
    let logPrinted = false

    nodeDataSteps.value?.steps?.forEach(async (step: any) => {
        if (typeof step.process === 'string' && step.process?.startsWith("/api/processes/") && !logPrinted) {
            const processData = await processStore.fetchProcessById(step.process.split('/').pop())

            step.processColor = processData.color
            step.process = processData.name
            localSteps.push(step)

            logPrinted = true
        } else {
            const processData = step.process

            step.processColor = processData.color
            localSteps.push(step)
        }
    })

    return localSteps
})

const fetchRelevantDppsForSteps = async (fromDppsId?: any) => {
    for (const productStep of activeStepsData.value) {
        if (!productStep.parentStepIds?.length) {
            continue
        }

        if (fromDppsId) {
            const dppsData = await useDppStore().fetchDppByIds(fromDppsId, productStep.parentStepIds)

            selectedLogistics.value[productStep.id] = dppsData.dpps

            const hasProductSteps = dppsData.dpps?.some((dppItem: any) => dppItem.productSteps?.length)

            if (!hasProductSteps) {
                continue
            }
        }

        const parentIds = (nodeData.value.parents || []).map((parent: any) => parent.id)
        const checkLogisticsItem = nodeData.value.logisticsItem?.length > 0

        if (parentIds?.length && checkLogisticsItem) {
            dppsByNode.value = await nodeStore.getDppsByNodeIds(parentIds, productStep.parentStepIds)

            if (dppsByNode.value?.dpps) {
                dpps.value[productStep.id] = dppsByNode.value.dpps.map((dppItem: any) => {
                    return {
                        title: `${dppItem.name} - ${dppItem.id}`,
                        value: dppItem.id,
                    }
                })
            }
        }
    }
}

const toggleStep = async (step: any) => {
    if (activeSteps.value.find(stepId => stepId === step.id)) {
        activeSteps.value = activeSteps.value.filter(stepId => stepId !== step.id)
        activeStepsData.value = activeSteps.value.filter(stepId => stepId !== step)
    } else {
        activeSteps.value.push(step.id)
        activeStepsData.value.push(step)
    }

    if (nodeData.value.parents?.length !== 0) {
        await fetchRelevantDppsForSteps()
    }
}

const duplicateInput = (step, inputId: string) => {
    const input = inputs.value.find((inputItem: any) => inputItem.id === inputId)

    step.inputs.push({
        ...input,
        id: `${input.id}_${Date.now()}`,
        additional: true,
    })
}

const isSuccess = (response: any) => {
    return !!response
}

const createInputsDpp = async (inputsData: any, dppId: string, dppProduct: any) => {
    const productSteps = new Set()
    const inputSteps = new Set()

    for (const input in inputsData) {
        if (!inputsData[input].stepId) {
            continue
        }

        const productStep = typeDpp.value !== 'ProductStep'
            ? dppProduct.productSteps.find(step => step.stepTemplateReference.split('/').pop() === inputsData[input].stepId).id
            : dppId

        let stepConfirmedInput = false

        if (!inputsData[input].type) {
            continue
        }

        const type = inputsData[input].type.toLowerCase().replace(/\s+/g, '')
        const stepFind = nodeData.value?.steps.find((step: any) => step.id === inputsData[input].stepId)
        const inputUnit = stepFind?.inputs.find((inputItem: any) => inputItem.id === inputsData[input].id)

        const previousValue = previousInputData.value[input]?.inputValue
        const currentValue = inputsData[input].inputValue
        const isChanged = JSON.stringify(previousValue) !== JSON.stringify(currentValue)

        switch (type) {
            case 'image':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                            inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)

                        if (inputResponse && inputsData[input].inputValue[i].url && inputsData[input].inputValue[i] !== MEASUREMENT_TYPE) {
                            const image = b64toBlob(inputsData[input].inputValue[i].url)
                            const imageType = mimedb[image.type]

                            const formData = new FormData()

                            formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)

                            if (inputsData[input].dppImge) {
                                await useProductsInputsStore().deleteProductInputImage(inputsData[input].productInputId)
                                await useProductsInputsStore().createProductInputImage(inputResponse.id, formData)
                            } else {
                                await useProductsInputsStore().createProductInputImage(inputResponse.id, formData)
                            }
                        }
                    }
                } else {
                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                        additional: inputsData[input].additional,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)

                    if (inputResponse && inputsData[input].inputValue.url && inputsData[input].inputValue !== MEASUREMENT_TYPE) {
                        const image = b64toBlob(inputsData[input].inputValue.url)
                        const imageType = mimedb[image.type]

                        const formData = new FormData()

                        formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)

                        if (inputsData[input].dppImge) {
                            await useProductsInputsStore().deleteProductInputImage(inputsData[input].productInputId)
                            await useProductsInputsStore().createProductInputImage(inputResponse.id, formData)
                        } else {
                            await useProductsInputsStore().createProductInputImage(inputResponse.id, formData)
                        }
                    }
                }
                break

            case 'images':
                if (Array.isArray(inputsData[input].inputValue)) {
                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        if (inputResponse && inputsData[input].inputValue[i] !== MEASUREMENT_TYPE) {
                            if (inputsData[input].inputValue[i]?.startsWith('data:image')) {
                                const formData = new FormData()
                                const image = b64toBlob(inputsData[input].inputValue[i])
                                const imageType = mimedb[image.type]

                                formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                                formData.append('input', `/api/product_inputs/${inputResponse.id}`)
                                await useProductsInputsStore().createProductInputImages(formData)
                            } else {
                                const productInputImages = await useProductsInputsStore().fetchProductInputImages(inputsData[input].productInputId)

                                const matchingImages = productInputImages.filter((imageObj: any) =>
                                    !inputsData[input].inputValue.some((url: any) => url.endsWith(`/${imageObj.image}`)),
                                )

                                matchingImages.map(async (item: any) => {
                                    await useProductsInputsStore().deleteProductInputImages(item.id)
                                })
                            }
                        }
                    }
                } else {
                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                        additional: inputsData[input].additional,
                    }

                    let inputResponse
                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)

                    if (inputResponse && inputsData[input].inputValue?.startsWith('data:image') && inputsData[input].inputValue !== MEASUREMENT_TYPE) {
                        const formData = new FormData()
                        const image = b64toBlob(inputsData[input].inputValue)
                        const imageType = mimedb[image.type]

                        formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                        formData.append('input', `/api/product_inputs/${inputResponse.id}`)

                        await useProductsInputsStore().createProductInputImages(formData)
                    }
                }
                break

            case 'file':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                            inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)

                        if (inputResponse && typeof inputsData[input].inputValue[i] !== 'string' && inputsData[input].inputValue[i] !== MEASUREMENT_TYPE) {
                            if (inputsData[input].productInputId) {
                                await useProductsInputsStore().deleteProductInputDocument(inputsData[input].productInputId)
                            }
                            await useProductsInputsStore().createProductInputDocument(inputResponse.id, inputsData[input].inputValue[i])
                        }

                        if (productInputFile.value[inputsData[input].id] && !inputsData[input].inputValue[i]) {
                            await useProductsInputsStore().deleteProductInputDocument(inputsData[input].productInputId)
                        }
                    }
                } else {
                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                        additional: inputsData[input].additional,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)

                    if (inputResponse && typeof inputsData[input].inputValue !== 'string' && inputsData[input].inputValue !== MEASUREMENT_TYPE) {
                        if (inputsData[input].productInputId) {
                            await useProductsInputsStore().deleteProductInputDocument(inputsData[input].productInputId)
                        }
                        await useProductsInputsStore().createProductInputDocument(inputResponse.id, inputsData[input].inputValue)
                    }

                    if (productInputFile.value[inputsData[input].id] && !inputsData[input].inputValue) {
                        await useProductsInputsStore().deleteProductInputDocument(inputsData[input].productInputId)
                    }
                }
                break

            case 'text':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            id: inputsData[input].id,
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            textValue: inputsData[input].inputValue[i],
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit && inputsData[input].isNew) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                            inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        id: inputsData[input].id,
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        textValue: inputsData[input].inputValue,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit && inputsData[input].isNew) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'textarea':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            textAreaValue: inputsData[input].inputValue[i],
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                            inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        textAreaValue: inputsData[input].inputValue,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                        additional: inputsData[input].additional,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit && inputsData[input].isNew) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'numerical':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const isMeasurementType = inputsData[input]?.inputValue?.[i] === MEASUREMENT_TYPE

                        const requestData = {
                            id: inputsData[input].id,
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            numericalValue: isMeasurementType ? 0 : Number.parseFloat(inputsData[input].inputValue[i]),
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit && inputsData[input].isNew) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                            inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const isMeasurementType = inputsData[input]?.inputValue === MEASUREMENT_TYPE

                    const requestData = {
                        id: inputsData[input].id,
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        numericalValue: isMeasurementType ? 0 : Number.parseFloat(inputsData[input].inputValue),
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit && inputsData[input].isNew) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'datetime':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const isMeasurementType = inputsData[input]?.inputValue?.[i] === MEASUREMENT_TYPE

                        const dateTimeTo = isMeasurementType
                            ? null
                            : inputsData[input].inputValue[i]?.length
                                ? inputsData[input].inputValue[i]
                                : inputsData[input].inputValue || null

                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            dateTimeTo,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                        }

                        if (inputsData[input].inputValue[i][0] || inputsData[input].inputValue[i][1]) {
                            let inputResponse

                            if (!inputsData[input].isEdit) {
                                inputResponse = await useProductsInputsStore().createProductInput(requestData)
                            }

                            if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                                inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                            }

                            stepConfirmedInput = isSuccess(inputResponse)
                        } else {
                            let inputResponse

                            if (!inputsData[input].isEdit) {
                                inputResponse = await useProductsInputsStore().createProductInput(requestData)
                            }

                            if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                                inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                            }

                            stepConfirmedInput = isSuccess(inputResponse)
                            break
                        }
                    }
                } else {
                    const isMeasurementType = inputsData[input]?.inputValue === MEASUREMENT_TYPE

                    const dateTimeTo = isMeasurementType
                        ? null
                        : inputsData[input].inputValue || null

                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        dateTimeTo,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                        additional: inputsData[input].additional,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId
                        && productInputDate.value[inputsData[input].id] !== inputsData[input].inputValue) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'datetimerange':
                if (Array.isArray(inputsData[input].inputValue) && inputsData[input].inputValue.length > 2) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const isMeasurementType = inputsData[input]?.inputValue?.[i] === MEASUREMENT_TYPE

                        const dateTimeFrom = isMeasurementType
                            ? null
                            : inputsData[input]?.inputValue?.[i]?.length < 1
                                ? inputsData[input]?.inputValue?.[i]?.[0]
                                : inputsData[input]?.inputValue?.[0] || null

                        const dateTimeTo = isMeasurementType
                            ? null
                            : inputsData[input]?.inputValue?.[i]?.length < 1
                                ? inputsData[input]?.inputValue?.[i]?.[1]
                                : inputsData[input]?.inputValue?.[1] || null

                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            dateTimeFrom,
                            dateTimeTo,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                        }

                        if (inputsData[input].inputValue[i][0] || inputsData[input].inputValue[i][1]) {
                            let inputResponse

                            if (!inputsData[input].isEdit) {
                                inputResponse = await useProductsInputsStore().createProductInput(requestData)
                            }

                            stepConfirmedInput = isSuccess(inputResponse)
                        } else {
                            let inputResponse

                            if (!inputsData[input].isEdit) {
                                inputResponse = await useProductsInputsStore().createProductInput(requestData)
                            }

                            if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                                inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                            }

                            stepConfirmedInput = isSuccess(inputResponse)
                            break
                        }
                    }
                } else {
                    const isMeasurementType = inputsData[input]?.inputValue === MEASUREMENT_TYPE

                    const dateTimeFrom = isMeasurementType
                        ? null
                        : inputsData[input].inputValue[0] || null

                    const dateTimeTo = isMeasurementType
                        ? null
                        : inputsData[input].inputValue[1] || null

                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        dateTimeFrom,
                        dateTimeTo,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                        additional: inputsData[input].additional,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit && inputsData[input].isNew) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isNew) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'coordinates':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const isMeasurementType = inputsData[input]?.inputValue?.[i] === MEASUREMENT_TYPE

                        const latitudeValue = isMeasurementType
                            ? 0
                            : inputsData[input].inputValue[i].lat || 0

                        const longitudeValue = isMeasurementType
                            ? 0
                            : inputsData[input].inputValue[i].lng || 0

                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            latitudeValue,
                            longitudeValue,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                            inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const isMeasurementType = inputsData[input]?.inputValue === MEASUREMENT_TYPE

                    const latitudeValue = isMeasurementType
                        ? 0
                        : inputsData[input].inputValue.lat || 0

                    const longitudeValue = isMeasurementType
                        ? 0
                        : inputsData[input].inputValue.lng || 0

                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        latitudeValue,
                        longitudeValue,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        updatable: inputsData[input].updatableInput || false,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                        additional: inputsData[input].additional,
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }

                break

            case 'checkboxlist':
                if (Array.isArray(inputsData[input].inputValue)
                    && inputsData[input].inputValue.every(val => Array.isArray(val))
                ) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            checkboxValue: Array.isArray(inputsData[input]?.inputValue?.[i])
                                ? inputsData[input].inputValue[i]
                                : [],
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            measurementValue: Number(inputsData[input][i].measurementValueInputs) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                            inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        checkboxValue: Array.isArray(inputsData[input].inputValue) ? inputsData[input].inputValue : [],
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'radiolist':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            radioValue: inputsData[input].inputValue[i],
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            measurementValue: Number(inputsData[input][i].measurementValueInputs) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        if ((inputsData[input].updatableInput[i] || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                            inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        radioValue: inputsData[input].inputValue,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    if ((inputsData[input].updatableInput || inputsData[input].isEdit) && inputsData[input]?.productInputId) {
                        inputResponse = await useProductsInputsStore().updateProductInputById(inputsData[input].productInputId, requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'textlist':
                if (Array.isArray(inputsData[input].textList)
                    && inputsData[input].textList.every(val => Array.isArray(val))
                ) {
                    for (let i = 0; i < inputsData[input].textList.length; i++) {
                        const requestData = {
                            dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            textValue: inputsData[input].textList[i].join(', '),
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            isConfirmFilled: isChanged || inputsData[input].isNew,
                            additional: i !== 0,
                            measurementValue: Number(inputsData[input][i].measurementValueInputs) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                        }

                        let inputResponse

                        if (!inputsData[input].isEdit) {
                            inputResponse = await useProductsInputsStore().createProductInput(requestData)
                        }

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: typeDpp.value !== 'ProductStep' ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        textValue: inputsData[input].textList.join(', '),
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        isConfirmFilled: isChanged || inputsData[input].isNew,
                        additional: inputsData[input].additional,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    let inputResponse

                    if (!inputsData[input].isEdit) {
                        inputResponse = await useProductsInputsStore().createProductInput(requestData)
                    }

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break
        }

        if (stepConfirmedInput && !isSubmited.value) {
            // This request takes an awfully long time on locale 17.48s (PATCH)
            // You should probably contact BE

            if (productStepsId.has(dpp.value.id)) {
                continue
            }

            productStepsId.add(dpp.value.id)

            const logistics = formValues.value.logistics[inputsData[input].stepId]
            const productStepsDppData = formValues.value.productStepsDpp[inputsData[input].stepId]
            const productStepsDppPreviousData = formValues.value.dpp[inputsData[input].stepId]

            const apiUrlProcessedMaterials = showProductStepsInput.value[inputsData[input].stepId]
                ? Array.isArray(productStepsDppData)
                    ? productStepsDppData.map((productStepDpp: string) => `/api/product_steps/${productStepDpp}`)
                    : []
                : productStepsDppPreviousData
                    ? [`/api/product_steps/${productStepsDppPreviousData}`]
                    : []

            const processedMaterials = apiUrlProcessedMaterials
            const { ongoingDpp: stateOngoingDpp, createEmptyDpp: stateCreateEmptyDpp } = getStateByStep(inputsData[input].stepId)
            let state: string | null = null
            let productStapeId: string | null = null
            const unitSymbol = nodeData.value?.steps.find(step => step.id === inputsData[input].stepId).unitSymbol

            if (stateOngoingDpp) {
                state = 'ONGOING_DPP'
            } else if (stateCreateEmptyDpp) {
                state = 'EMPTY_DPP'
            } else if (formValues.value.logistics[inputsData[input].stepId]) {
                state = 'NOT_ASSIGNED'
            } else {
                state = 'NOT_ASSIGNED'
            }

            if (dpp.value['@type'] === 'Dpp') {
                dpp.value?.productSteps.forEach((productStepItem: any) => {
                    const stepId = productStepItem.stepTemplateReference.split('/').pop()
                    if (inputsData[input].stepId === stepId) {
                        productStapeId = productStepItem.id
                    }
                })
            } else {
                productStapeId = dpp.value.id
            }

            if (!productStapeId) {
                return
            }

            await useProductsStore().updateProductStepById(productStapeId, {
                unitMeasurement: inputsData[input].unitMeasurement || null,
                measurementType: inputsData[input].measurementType || null,
                measurementValue: Number.parseFloat(inputsData[input].measurementValue) || null,
                unitSymbol: unitSymbol || '',
                processedMaterials,
                materialsReceivedFrom: logistics ? [`/api/logistics/${logistics}`] : [],
                ongoingDpp: stateOngoingDpp,
                createEmptyDpp: stateCreateEmptyDpp,
                state,
            })

            productSteps.add(productStep)
            inputSteps.add(inputsData[input].stepId)
        }
    }
}

const addDPP = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        useNuxtApp().$event('message', {
            type: 'error',
            message: t('messages.companyReq'),
            title: 'Error',
        })

        return
    }

    try {
        const stepsId = activeSteps.value.map((id: string) => `/api/steps/${id}`)
        const productSteps: string[] = []

        if (typeDpp.value !== 'ProductStep') {
            const dppResponse = await useDppStore().patchDpp(dpp.value.id, {
                company: `/api/companies/${formValues.value.company}`,
                companySite: formValues.value.site && formValues.value.site !== 0 ? `/api/company_sites/${formValues.value.site}` : null,
                createEmptyDpp: dppCreationStatus.value.emptyDpp,
                ongoingDpp: dppCreationStatus.value.ongoingDpp,
                productSteps,
                qrId: formValues.value.qrId || dpp.value.qrId,
                steps: stepsId || [],
                user: `/api/users/${userId.value}`,
                state: !dppCreationStatus.value.emptyDpp && !dppCreationStatus.value.ongoingDpp ? 'NOT_ASSIGNED' : 'EMPTY_DPP',
            })

            await createInputsDpp(inputData.value, dppResponse.id, dpp.value)

            dpp.value.productSteps.forEach(async (productStep: any) => {
                const stepId = productStep.stepTemplateReference.split('/').pop()
                const { ongoingDpp: stateOngoingDpp, createEmptyDpp: stateCreateEmptyDpp } = getStateByStep(stepId)
                let state: string | null = null

                if (stateOngoingDpp) {
                    state = 'ONGOING_DPP'
                } else if (stateCreateEmptyDpp) {
                    state = 'EMPTY_DPP'
                } else {
                    state = 'NOT_ASSIGNED'
                }

                await useProductsStore().updateProductStepById(productStep.id, {
                    createEmptyDpp: stateCreateEmptyDpp,
                    ongoingDpp: stateOngoingDpp,
                    state,
                })
            })
        } else {
            let state: string | null = null

            const logistics = formValues.value.logistics[stepsId[0].split('/').pop()]
            const productStepsDppData = formValues.value.productStepsDpp[stepsId[0].split('/').pop()]

            const processedMaterials = productStepsDppData ? productStepsDppData.map((productStepDpp: string) => `/api/product_steps/${productStepDpp}`) : []

            const { ongoingDpp: stateOngoingDpp, createEmptyDpp: stateCreateEmptyDpp } = getStateByStep(dpp.value.stepTemplateReference.id)

            if (stateOngoingDpp) {
                state = 'ONGOING_DPP'
            } else if (stateCreateEmptyDpp) {
                state = 'EMPTY_DPP'
            } else {
                state = 'NOT_ASSIGNED'
            }
            const productTemplateStep = dpp.value.stepTemplateReference.id
            const stepData = nodeData.value?.steps.find(step => step.id === productTemplateStep)

            const dppResponse = await productsStore.updateProductStepById(dpp.value.id, {
                company: `/api/companies/${formValues.value.company}`,
                companySite: formValues.value.site && formValues.value.site !== 0 ? `/api/company_sites/${formValues.value.site}` : null,
                createEmptyDpp: stateCreateEmptyDpp,
                ongoingDpp: stateOngoingDpp,
                qrId: formValues.value.qrId || dpp.value.qrId,
                user: `/api/users/${userId.value}`,
                processedMaterials,
                materialsReceivedFrom: logistics ? [`/api/logistics/${logistics}`] : [],
                state,
                unitMeasurement: stepData?.unitMeasurement || null,
                measurementType: stepData?.measurementType || null,
                measurementValue: Number.parseFloat(formValues.value.measurementValueProductSteps[productTemplateStep]) || null,
                unitSymbol: stepData.unitSymbol || '',
            })

            await createInputsDpp(inputData.value, dppResponse.id, dpp.value)
        }

        isConfirmFilled.value = false

        useNuxtApp().$event('message', {
            type: 'success',
            message: t('messages.editDpp'),
            title: 'Success',
        })

        useNuxtApp().$event('dppEdited', true)
        router.go(0)
        closeEditDppModal()
    } catch (error) {
        console.error('Error creating DPP:', error)
        useNuxtApp().$event('message', {
            type: 'error',
            message: t('messages.errorDppCreation'),
            title: 'Error',
        })
    }
}

const confirmFilled = (stepIndex: number, stepId: string) => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        const message: MessageBag = {
            type: 'error',
            message: t('messages.companyReq'),
            title: 'Error',
        }

        useNuxtApp().$event('message', message)

        return
    }

    const inputMap: Record<string, any> = {}
    const step = nodeData.value.steps?.find((s: any) => s.id === stepId)
    const hasInputs = step.inputs && step.inputs.length > 0

    if (!hasInputs) {
        const message: MessageBag = {
            type: 'info',
            message: t('messages.confirmInput'),
            title: 'Info',
        }

        useNuxtApp().$event('message', message)

        return
    }

    for (const key in formValues.value.inputs) {
        const value = formValues.value.inputs[key]
        const baseId = key.split('_')[0]

        if (!inputMap[baseId]) {
            inputMap[baseId] = {
                id: baseId,
                name: null,
                stepId: null,
                stepName: null,
                inputValue: [],
                measurementValueInputs: [],
                updatableInput: [],
                type: null,
                inputCategories: null,
                isConfirmFilled: false,
                unitMeasurement: null,
                measurementType: null,
                logistics: null,
                dpp: null,
                productStepsDpp: null,
                isNew: false,
                isModified: false,
                productInputId: prouctInputIds.value[baseId] || null,
            }
        }

        inputMap[baseId].inputValue.push(value)
        inputMap[baseId].measurementValueInputs.push(formValues.value.measurementValue[key])
    }

    activeStepsData.value.forEach((step: any) => {
        if (!step?.inputs || step?.inputs.length === 0) {
            return
        }

        step.inputs?.forEach((input: any) => {
            if (inputMap[input.id]) {
                inputMap[input.id].stepId = step.id
                inputMap[input.id].stepName = step.name
                inputMap[input.id].unitMeasurement = step.unitMeasurement
                inputMap[input.id].measurementType = step.measurementType
                inputMap[input.id].measurementValue = formValues.value.measurementValueProductSteps[step.id]
                inputMap[input.id].name = input.name
                inputMap[input.id].type = input.type
                inputMap[input.id].inputCategories = input.categories || []
                inputMap[input.id].isEdit = lockedInputs.value[input.id] === true
                inputMap[input.id].productInputId = prouctInputIds.value[input.id]
                inputMap[input.id].isNew = !prouctInputIds.value[input.id]
                inputMap[input.id].additional = input?.additional
                inputMap[input.id].updatableInput = input.updatable
            }

            if (input.type === 'textList') {
                if (!inputMap[input.id]) {
                    inputMap[input.id] = {}
                }

                inputMap[input.id].stepId = step.id
                inputMap[input.id].stepName = step.name
                inputMap[input.id].unitMeasurement = step.unitMeasurement
                inputMap[input.id].measurementType = step.measurementType
                inputMap[input.id].measurementValue = formValues.value.measurementValueProductSteps[step.id]
                inputMap[input.id].name = input.name
                inputMap[input.id].type = input.type
                inputMap[input.id].inputCategories = input.categories || []
                inputMap[input.id].textList = input.options || []
                inputMap[input.id].isEdit = false
                inputMap[input.id].updatableInput = input.updatable
            }
        })
    })

    for (const key in inputMap) {
        if (inputMap[key].inputValue?.length === 1) {
            inputMap[key].inputValue = inputMap[key].inputValue[0]
        }

        if (inputMap[key].measurementValueInputs?.length === 1) {
            inputMap[key].measurementValueInputs = inputMap[key].measurementValueInputs[0]
        }

        if (inputMap[key].updatableInput?.length === 1) {
            inputMap[key].updatableInput = inputMap[key].updatableInput[0]
        }

        // Validate and lock the input on confirmation of the step if it has a value
        if (isInputFilled(inputMap[key].inputValue)) {
            inputMap[key].locked = true
        }

        const previousValue = previousInputData.value[key]?.inputValue
        if (previousValue && JSON.stringify(previousValue) !== JSON.stringify(inputMap[key].inputValue)) {
            inputMap[key].isModified = true
        }

        inputMap[key].isConfirmFilled = inputMap[key].isNew || inputMap[key].isModified
    }

    confirmedSteps.value[stepIndex] = true
    previousInputData.value = { ...inputMap }
    inputData.value = inputMap

    if (formValues.value.logistics[stepId] && !formValues.value.dpp[stepId]) {
        formValues.value.logistics[stepId] = null
    }

    if (formValues.value.logistics[stepId] && formValues.value.dpp[stepId] && !formValues.value.productStepsDpp[step.id] && showProductStepsInput.value[step.id]) {
        formValues.value.logistics[stepId] = null
        formValues.value.dpp[stepId] = null
    }

    const { validationResult } = validateSteps(nodeData.value.steps, inputData.value)
    const hasValidStep = validationResult.some((result: any) => result.isValid)
    const allValid = validationResult.every((result: any) => result.isEmptyAllInputs)

    stepsState.value = validationResult

    if (hasValidStep) {
        dppCreationStatus.value = {
            createDpp: true,
            ongoingDpp: false,
            emptyDpp: false,
            btnLable: t('dpps.save'),
        }
    } else {
        dppCreationStatus.value = {
            createDpp: false,
            ongoingDpp: false,
            emptyDpp: true,
            btnLable: t('dpps.createEmptyDpp'),
        }
    }

    if (!allValid) {
        dppCreationStatus.value = {
            createDpp: true,
            ongoingDpp: false,
            emptyDpp: false,
            btnLable: t('dpps.save'),
        }
    }

    Object.values(inputData.value).forEach((inputItem: any) => {
        if (inputItem.type === 'textList') {
            const stateClass = getStateByStep(inputItem.stepId)?.class

            inputItem.isEdit = stateDpp.value === 'EMPTY_DPP'
                ? !['ongoing', 'finish'].includes(stateClass)
                : true
        }
    })

    isConfirmFilled.value = true

    const message: MessageBag = {
        type: 'success',
        message: t('messages.logisticsFillInData'),
        title: 'Success',
    }

    useNuxtApp().$event('message', message)
}

watch(() => formValues.value.company, newCompanyId => {
    if (!newCompanyId) {
        sites.value = []
        isLoading.value.site = false

        return
    }

    const selectedCompany = companies.value?.find(company => company.value === newCompanyId)

    if (selectedCompany?.sites) {
        isLoading.value.site = true
    }

    sites.value = [
        {
            title: t('noSite'),
            value: 0,
        },
        ...(selectedCompany?.sites?.map((site: any) => ({
            title: site.name,
            value: site.id,
        })) || []),
    ]

    isLoading.value.site = false
})

const addInputByEdit = (index: number) => {
    $event('openDppAddInputModal', { indexStep: index, type: 'edit-dpp' })
}

/* eslint-disable @typescript-eslint/no-use-before-define */
const handleLogisticsChange = async (stepId: string, newValue: any, isDppsChange: boolean = false) => {
    if ((!selectedLogistics.value || !selectedLogistics.value[stepId]) && selectedFromProductSteps.value[newValue]?.fromProductSteps) {
        const matchedTitles = selectedFromProductSteps.value[newValue]?.fromProductSteps.map((url: string) => {
            const id = url.split('/').pop()

            return dpps.value[stepId].find((item: any) => item.value === id)?.value
        }).filter(Boolean)

        formValues.value.dpp[stepId] = matchedTitles[0] || null

        return
    }
    const fromDppsByLogistics = fromDpps.value.logistics?.filter(logistic => logistic.id === newValue)

    if (fromDppsByLogistics?.length) {
        await fetchRelevantDppsForSteps(fromDppsByLogistics[0].fromDpps)
    }

    selectedDpps.value[stepId] = selectedLogistics.value[stepId]

    dpps.value[stepId] = []
    formValues.value.dpp[stepId] = null
    formValues.value.productStepsDpp[stepId] = null
    productStepsDpp.value[stepId] = []
    showProductStepsInput.value[stepId] = false

    if (isDppsChange) {
        await handleDppsChange(stepId, selectedDpps.value[stepId]?.[0]?.id)
    }

    const filteredDpps = selectedDpps.value[stepId]
        ?.filter((dppItem: any) => dppItem.productSteps?.length)
        .map((dppItem: any) => ({
            title: `${dppItem.name} - ${dppItem.id}`,
            value: dppItem.id,
        }))

    dpps.value[stepId] = filteredDpps

    if (!filteredDpps?.length) {
        formValues.value.dpp[stepId] = null
        formValues.value.productStepsDpp[stepId] = null
        productStepsDpp.value[stepId] = []
        showProductStepsInput.value[stepId] = false
    }
}

const handleDppsChange = async (stepId: string, newValue: any) => {
    const dppData = dppsByNode.value.dpps.find(dppItem => dppItem.id === newValue)
    if (!dppData) {
        return
    }

    formValues.value.logistics[stepId] = dppData.logistics?.id ?? null

    const selectedDpp = selectedDpps.value[stepId]?.find(d => d.id === newValue)

    formValues.value.productStepsDpp[stepId] = null
    productStepsDpp.value[stepId] = []
    showProductStepsInput.value[stepId] = false

    if (!selectedDpp) {
        await handleLogisticsChange(stepId, dppData.logistics?.id, true)
        formValues.value.dpp[stepId] = selectedLogistics.value[stepId][0].id
        showProductStepsInput.value[stepId] = productStepsDpp.value[stepId]?.length > 0
    }

    if (selectedDpp?.productSteps?.length) {
        showProductStepsInput.value[stepId] = true
        productStepsDpp.value[stepId] = selectedDpp.productSteps.map((ps: any) => ({
            title: ps.name,
            value: ps.id,
        }))

        const a = Object.values(prevProductStepSelections).flat()

        productStepsDpp.value[stepId] = productStepsDpp.value[stepId].filter(
            (item: any) => !a.includes(item.value),
        )
    }
}
/* eslint-enable @typescript-eslint/no-use-before-define */

const handleProductStepsDpp = (stepId: string, newValue: string[]) => {
    prevProductStepSelections[stepId] = [...newValue]
}

const history = (productInputId: string) => {
    $event('openHistoryInputModal', productInputId)
}

$listen('handleDppEditInputSubmitted', input => {
    const stepIndex = input.indexStep

    if (!dynamicallyAddedInputs.value[stepIndex]) {
        dynamicallyAddedInputs.value[stepIndex] = []
    }

    const existingInput = nodeData.value.steps[stepIndex].inputs.find(i => i.id === input.id)
    if (existingInput) {
        return
    }

    nodeData.value?.steps[input.indexStep].inputs.push({
        id: input.id,
        type: input.inputType,
        name: input.label,
        additional: true,
    })

    dynamicallyAddedInputs.value[stepIndex].push(input.id)
})
</script>

<template>
    <ModalLayout
        :is-open="isEditDppModalOpen"
        name="simple-modal"
        :title="`Edit DPP: ${nodeData?.name}`"
        no-buttons
        width="70vw"
        style="overflow-y: scroll;"
        @modal-close="closeEditDppModal"
    >
        <template #content>
            <VContainer class="w-100">
                <p
                    v-if="inputs[0]"
                    class="num_of_inputs mb-5"
                >
                    {{ $t('dpps.numberOfInputs') }}: {{ inputs.length }}
                </p>

                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VRow>
                        <VCol
                            cols="12"
                            sm="6"
                        >
                            <VTextField
                                v-model="formValues.idQr"
                                :label="$t('dpps.idQr')"
                                variant="outlined"
                                required
                            />
                        </VCol>
                        <VCol
                            cols="12"
                            sm="6"
                        >
                            <VSelect
                                v-model="formValues.company"
                                :label="$t('dpps.company')"
                                variant="outlined"
                                :items="companies"
                                :rules="companyRules"
                                :loading="isLoading.company"
                                :no-data-text="isLoading.company ? $t('dpps.loadingCompanies') : $t('noDataAvailable')"
                            />
                        </VCol>
                        <VCol
                            cols="12"
                            sm="6"
                        >
                            <VSelect
                                v-model="formValues.site"
                                :label="$t('dpps.site')"
                                variant="outlined"
                                :items="sites"
                                :loading="isLoading.site"
                                :no-data-text="isLoading.site ? $t('dpps.loadingSites') : $t('noDataAvailable')"
                            />
                        </VCol>
                        <VCol
                            :class="nodeParentsData?.length ? '' : 'd-none'"
                            cols="12"
                            sm="6"
                        >
                            <VSelect
                                v-model="formValues.logistic"
                                :label="$t('dpps.logistic')"
                                :placeholder="$t('dpps.selectLogistic')"
                                variant="outlined"
                                :items="logisticsItem"
                            />
                        </VCol>
                    </VRow>
                    <div v-if="nodeDataSteps">
                        <div
                            v-for="(step, index) in nodeDataSteps.steps"
                            :key="step.id"
                        >
                            <VRow>
                                <VCol
                                    cols="12"
                                    class="step-checkbox"
                                    :class="{ 'no-border': index === 0 }"
                                >
                                    <VCheckbox
                                        :model-value="activeSteps.find(id => id === step.id)"
                                        :value="step.id"
                                        :label="step.name"
                                        :disabled="confirmedSteps[index]"
                                        @click="toggleStep(step)"
                                    >
                                        <template #label>
                                            <span>{{ step.name }} <span v-if="activeSteps.includes(step.id)">({{ $t('state') }}:  <strong :class="getStateByStep(step.id).class">{{ `${getStateByStep(step.id).title}` }}</strong> )</span></span>
                                        </template>
                                    </VCheckbox>
                                </VCol>

                                <template v-if="activeSteps.includes(step.id)">
                                    <VCol
                                        :key="step.id"
                                        cols="12"
                                        sm="6"
                                        class="my-0 py-0 pb-4"
                                    >
                                        <VSelect
                                            v-model="formValues.logistics[step.id]"
                                            :label="t('dpps.logistic')"
                                            :placeholder="t('dpps.selectLogistic')"
                                            variant="outlined"
                                            :items="nodeData.logisticsItem"
                                            :disabled="confirmedSteps[index] || lockedInputs[step.id]"
                                            @update:model-value="(val) => handleLogisticsChange(step.id, val)"
                                        />
                                    </VCol>

                                    <VCol
                                        :key="step.id"
                                        cols="12"
                                        sm="6"
                                        class="my-0 py-0 pb-4"
                                    >
                                        <VSelect
                                            v-model="formValues.dpp[step.id]"
                                            :label="t('dpps.previousDpp')"
                                            :placeholder="t('dpps.previousDpp')"
                                            variant="outlined"
                                            :items="dpps[step.id]"
                                            :disabled="confirmedSteps[index] || lockedInputs[step.id]"
                                            @update:model-value="(val) => handleDppsChange(step.id, val)"
                                        />
                                    </VCol>
                                    <VCol
                                        v-if="showProductStepsInput[step.id]"
                                        :key="step.id"
                                        cols="12"
                                        sm="6"
                                        class="my-0 py-0 pb-4"
                                    >
                                        <VSelect
                                            v-model="formValues.productStepsDpp[step.id]"
                                            :label="t('dpps.productStepDpp')"
                                            :placeholder="t('dpps.productStepDpp')"
                                            variant="outlined"
                                            :items="productStepsDpp[step.id]"
                                            multiple
                                            :disabled="confirmedSteps[index] || lockedInputs[step.id]"
                                            @update:model-value="(val) => handleProductStepsDpp(step.id, val)"
                                        />
                                    </VCol>
                                    <VCol
                                        v-if="step.batchTypeOfStep === 'BATCH' && step.measurementType"
                                        :key="step.id"
                                        cols="12"
                                        sm="6"
                                        class="my-0 py-0 pb-4"
                                    >
                                        <VTextField
                                            v-model="formValues.measurementValueProductSteps[step.id]"
                                            :label="`${step.measurementType === 'batchQuantity' ? $t('dpps.batchQuantity') : formatPascalCaseToLabel(step.measurementType)}${step.unitMeasurement ? ` (${step.unitMeasurement})` : ''}`"
                                            variant="outlined"
                                            type="number"
                                            required
                                            :disabled="confirmedSteps[index]"
                                        >
                                            <template #append-inner>
                                                {{ step.unitSymbol }}
                                            </template>
                                        </VTextField>
                                    </VCol>
                                    <VCol
                                        v-for="input in step.inputs.filter(i => !i.additional)"
                                        :key="input.id"
                                        cols="12"
                                        sm="6"
                                        class="my-0 py-0 pb-4"
                                    >
                                        <InputByType
                                            v-model="formValues.inputs[input.id]"
                                            :measurement-model-value="formValues.measurementValue[input.id]"
                                            :updatable-input="formValues.updatableInput[input.id]"
                                            :type="input.type"
                                            :name="input.name"
                                            :input="input"
                                            :data="formValues.inputs[input.id]"
                                            :is-disabled="!(formValues.updatableInput[input.id]) && (confirmedSteps[index] || lockedInputs[input.id])"
                                            :show-history="isUpdatable[input.id]"
                                            :show-updatable-input="false"
                                            @update:measurement-model-value="val => formValues.measurementValue[input.id] = val"
                                            @update:updatable-input="val => formValues.updatableInput[input.id] = val"
                                            @update:history-click="val => history(prouctInputIds[val])"
                                        />
                                        <VBtn
                                            v-if="!['textList', 'textlist'].includes(input.type)"
                                            class="duplicate-btn"
                                            variant="plain"
                                            :disabled="confirmedSteps[index]"
                                            @click="duplicateInput(step, input.id)"
                                        >
                                            <PhosphorIconPlusCircle
                                                :size="16"
                                                class="me-2 ms-0 ps-0"
                                            />
                                            {{ $t('dpps.duplicateInput') }}
                                        </VBtn>
                                    </VCol>

                                    <VCol
                                        v-if="step.inputs.some(i => i.additional)"
                                        cols="12"
                                        class="mt-4"
                                    >
                                        <VRow class="pa-4">
                                            <VRow style="width: 100%">
                                                <span class="additional-input-title">{{ $t('dpps.additionalInputs') }}:</span>
                                            </VRow>
                                            <VRow>
                                                <VCol
                                                    v-for="input in step.inputs.filter(i => i.additional)"
                                                    :key="input.id"
                                                    cols="12"
                                                    sm="6"
                                                    class="my-0 py-0 pb-4"
                                                >
                                                    <InputByType
                                                        v-model="formValues.inputs[input.id]"
                                                        :measurement-model-value="formValues.measurementValue[input.id]"
                                                        :updatable-input="formValues.updatableInput[input.id]"
                                                        :type="input.type"
                                                        :name="input.name"
                                                        :input="input"
                                                        :data="formValues.inputs[input.id]"
                                                        :is-disabled="!(formValues.updatableInput[input.id]) && (confirmedSteps[index] || lockedInputs[input.id])"
                                                        :show-history="isUpdatable[input.id]"
                                                        :show-updatable-input="false"
                                                        @update:measurement-model-value="val => formValues.measurementValue[input.id] = val"
                                                        @update:updatable-input="val => formValues.updatableInput[input.id] = val"
                                                        @update:history-click="val => history(prouctInputIds[val])"
                                                    />
                                                    <VBtn
                                                        v-if="!['textList', 'textlist'].includes(input.type)"
                                                        class="duplicate-btn"
                                                        variant="plain"
                                                        :disabled="confirmedSteps[index]"
                                                        @click="duplicateInput(step, input.id)"
                                                    >
                                                        <PhosphorIconPlusCircle
                                                            :size="16"
                                                            class="me-2 ms-0 ps-0"
                                                        />
                                                        {{ $t('dpps.duplicateInput') }}
                                                    </VBtn>
                                                </VCol>
                                            </VRow>
                                        </VRow>
                                    </VCol>
                                </template>
                            </VRow>

                            <template v-if="activeSteps.includes(step.id)">
                                <VRow>
                                    <VCol
                                        cols="6"
                                        class="my-5 py-0 pb-4"
                                    >
                                        <VBtn
                                            class="text-uppercase ma-auto d-block"
                                            color="#26A69A"
                                            variant="flat"
                                            size="large"
                                            height="45"
                                            width="90%"
                                            :disabled="confirmedSteps[index]"
                                            @click="addInputByEdit(index)"
                                        >
                                            {{ t('dpps.addInput') }}
                                        </VBtn>
                                    </VCol>

                                    <VCol
                                        cols="6"
                                        class="my-5 py-0 pb-4"
                                    >
                                        <VBtn
                                            class="text-uppercase ma-auto d-block"
                                            color="#26A69A"
                                            variant="flat"
                                            size="large"
                                            height="45"
                                            width="90%"
                                            :disabled="confirmedSteps[index]"
                                            @click="confirmFilled(index, step.id)"
                                        >
                                            {{ $t('dpps.confirmFilled') }}
                                        </VBtn>
                                    </VCol>
                                </VRow>
                            </template>
                        </div>
                    </div>
                </VForm>

                <div class="d-flex align-center justify-end mt-4 mb-4">
                    <VBtn
                        class="text-uppercase mx-3"
                        color="#26A69A"
                        variant="flat"
                        size="large"
                        height="45"
                        :disabled="dppCreationStatus.emptyDpp"
                        @click="addDPP(dppCreationStatus)"
                    >
                        {{ dppCreationStatus.btnLable }}
                    </VBtn>

                    <VBtn
                        class="text-uppercase"
                        color="#FF474A"
                        variant="flat"
                        size="large"
                        height="45"
                        @click="closeEditDppModal"
                    >
                        {{ $t('dpps.cancel') }}
                    </VBtn>
                </div>

                <TreeFlow
                    :data="stepsNode"
                    connection-key="none"
                    traversal="forward"
                    disable-options
                />
            </VContainer>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.num_of_inputs {
    font-size: 1.2rem;
    color: #26A69A;
    text-align: center;
}

.step-checkbox {
    padding-bottom: 0;
    border-top: 2px solid #26A69A;

    &.no-border {
        border-top: none;
    }

    .v-label{
        .empty {
            color: #a020f0;
        }

        .finish {
            color: #66ff07;
        }

        .ongoing {
            color: #ffa500;
        }
    }

}

.duplicate-btn {
    margin: 0;
    color: #26A69A;
    font-size: 10px;
    text-transform: none;
    padding: 0;
}

.additional-input-title {
    font-size: 1.2rem;
    color: #26A69A;
    padding-left: 10px;
    padding-bottom: 10px;
}
</style>
